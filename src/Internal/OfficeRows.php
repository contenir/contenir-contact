<?php

declare(strict_types=1);

namespace Contenir\Contact\Internal;

use Contenir\Contact\Office;
use Contenir\Contact\Offices;
use Contenir\Contact\Value\Geolocation;
use Contenir\Contact\Value\PostalAddress;

use function in_array;
use function is_array;

/**
 * Builds the offices from the rows of contact.office, over the business's
 * values.
 *
 * @internal
 */
final readonly class OfficeRows
{
    /**
     * @param array<array-key, mixed> $business
     */
    public function __construct(
        private array $business,
        private Reader $reader,
    ) {}

    /**
     * The offices, leaving out rows without a name and second rows with an
     * id already seen. The first row marked primary is the primary office,
     * or the first row when none is marked.
     *
     * @mago-expect analysis:mixed-assignment Settings are untyped; each row is checked here.
     */
    public function offices(mixed $rows): Offices
    {
        $kept    = [];
        $ids     = [];
        $primary = null;
        foreach (is_array($rows) ? $rows : [] as $row) {
            $row    = CmsRow::flat($row);
            $values = new SettingsArray($row);
            $name   = $values->text('name');
            $id     = '' === $values->text('id') ? $name : $values->text('id');
            if ('' === $name || in_array($id, $ids, strict: true)) {
                continue;
            }

            $ids[]   = $id;
            $kept[]  = $row;
            $primary = null === $primary && $values->flag('primary', default: false) ? $id : $primary;
        }

        $primary ??= $ids[0] ?? null;
        $offices = [];
        foreach ($kept as $index => $row) {
            $id        = $ids[$index] ?? '';
            $offices[] = $this->office($id, $row, $id === $primary);
        }

        return new Offices($offices);
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private function office(string $id, array $row, bool $primary): Office
    {
        $values  = new Inherited($this->business, $row);
        $details = $this->reader->details($values);

        return new Office(
            id: $id,
            name: (new SettingsArray($row))->text('name'),
            primary: $primary,
            search: $primary || (new SettingsArray($row))->flag('search', default: true),
            address: PostalAddress::fromRow($row, $this->reader->country),
            geolocation: Geolocation::from($row['geolocation'] ?? null),
            tradingName: $details['name'],
            legalName: $details['legalName'],
            taxId: $details['taxId'],
            type: $details['type'],
            additionalTypes: $details['additionalTypes'],
            telephone: $details['telephone'],
            email: $details['email'],
            hour: $details['hour'],
            detail: $details['detail'],
            overridden: $values->overridden(),
            cleared: $values->cleared(),
        );
    }
}
