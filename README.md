# contenir/contenir-contact

Framework-free contact details for Contenir sites: the business and its offices, read from the site's settings, for
page templates, Contact blocks and search engines alike.

- **The business**: trading name, legal name, ABN or other tax number, business type, and the telephone, email,
  opening hours and timezone its offices share.
- **Its offices**: each with an address, a position on the map, and any of the business's values overridden, or
  overridden with none.
- **Business types** from a bundled copy of the schema.org vocabulary, with additional types from Wikidata for the
  kinds schema.org lacks.
- **Formats**: named sets of values a site shows together, drawn by the site's templates or as plain markup.

The settings are edited in Site Settings > Office details. Mezzio sites read them through
[contenir/contenir-contact-mezzio](https://github.com/contenir/contenir-contact-mezzio), which gives templates a
`contact()` helper.

## Install

```bash
composer require contenir/contenir-contact
```

## Reading the settings

```php
$business = Contenir\Contact\ContactSettings::fromSettings($settings);

$business->name;                         // "Studio Esteta"
(string) $business->taxId;               // "ABN 12 345 678 901"
$business->offices->primary()?->address?->oneLine();

foreach ($business->offices as $office) {
    $office->name;                       // "Melbourne studio"
    (string) $office->telephone;         // "03 9428 0228", the office's own or the business's
    $office->telephone?->uri();          // "tel:+61394280228"
    $office->hour->summary();            // "Mon–Fri 9:00am–5:30pm"
    $office->hour->isOpenAt(new DateTimeImmutable());
}
```

The settings are a site's nested settings array. Setting names are singular, even for lists:

```php
'site' => ['title' => 'Studio Esteta', 'locale' => 'en_AU', 'timezone' => 'Australia/Melbourne'],
'contact' => [
    'business' => [
        'name'            => 'Studio Esteta',              // falls back on site.title
        'legal_name'      => 'Studio Esteta Pty Ltd',
        'tax_id'          => '12 345 678 901',
        'type'            => 'LocalBusiness',              // a schema.org organisation type
        'additional_type' => ['Q4387609'],                 // Wikidata entries
        'telephone'       => '+61 3 9428 0228',
        'email'           => 'studio@studioesteta.com.au',
        'timezone'        => 'Australia/Melbourne',        // falls back on site.timezone
        'hour'            => [
            ['day' => ['Mo', 'Tu', 'We', 'Th', 'Fr'], 'opens' => '09:00', 'closes' => '17:30'],
        ],
    ],
    'office' => [
        [
            'id'          => 'k3f9q2',                     // fixed when the office is added
            'name'        => 'Melbourne studio',
            'primary'     => true,
            'search'      => true,                         // shown to search engines
            'street'      => '402 Queens Parade',
            'locality'    => 'Fitzroy North',
            'region'      => 'VIC',
            'postcode'    => '3068',
            'country'     => 'AU',                         // falls back on the site's locale
            'geolocation' => ['latitude' => '-37.7787', 'longitude' => '144.9874'],
        ],
    ],
],
```

### Overrides

An office has the business's value for each of these unless its row has the key: `trading_name`, `legal_name`,
`tax_id`, `type`, `additional_type`, `telephone`, `email`, `hour`, `timezone`, `price_range`, `cuisine`, `menu`,
`reservation` and `star_rating`. A key with a value is the office's own; a key that is empty (`null`, `''` or `[]`)
means the office has none, whatever the business has. `$office->overridden` and `$office->cleared` name them.

The first office marked `primary` is the primary office, or the first office when none is. The primary office is
always shown to search engines. Rows without a name, and a second row with an id already seen, are left out.

### Opening hours

Each entry has its days, as schema.org's codes (`Mo` to `Su`), and an opening and closing time. Times are local to
the office's timezone, so `isOpenAt()` is right for a Perth office whoever reads it. A closing time before the
opening time runs past midnight; one the same as the opening time is open round the clock; `24:00` closes at
midnight. No entries is "by appointment" or "closed"; the site words it.

## Business types

`BusinessType` is a schema.org organisation type from the bundled vocabulary (schema.org 30.1). It knows its
ancestors (`isA('FoodEstablishment')`, `isLocalBusiness()`) and which properties it takes
(`takes('servesCuisine')`), so a law firm is never asked for its cuisine, and a plain Organization never gets opening
hours. `BusinessTypes::groups()` is the grouped list Office details offers; `BusinessType::exists()` checks a type
entered under Other.

`AdditionalTypes` lists kinds of business schema.org has no type for, each matched to its Wikidata entry
(Architecture firm, Interior design firm, Photography studio…), with `search()` for the picker.

`TypeDetail` holds the values only some types take: price range, cuisine, menu, reservations and star rating.

To update the vocabulary to a new schema.org release, download its JSON-LD file and run:

```bash
php bin/generate-vocabulary.php schemaorg-current-https.jsonld 30.2
```

## Contact blocks

A Contact block saves which offices it shows and how. `Offices::pick()` turns its choice into offices (all, the
primary, or the chosen ones in the editor's order), skipping any removed since; `OfficeChoice::missing()` names
those for the CMS to mark.

```php
$offices = $business->offices->pick($block['fields']['office']);
// ['choose' => 'all'] | ['choose' => 'primary'] | ['choose' => 'some', 'id' => ['k3f9q2', 'p8d1x7']]
```

## Formats

A format is a set of parts shown together: `name`, `trading_name`, `legal_name`, `tax_id`, `address`, `telephone`,
`email`, `hour` and `map`. The package has three (`full`, `address` and `compact`); a site adds its own, or replaces
these by name, in its config:

```php
'contenir_contact' => [
    'format' => [
        'header' => ['part' => ['telephone']],
        'footer' => ['part' => ['name', 'address'], 'template' => 'partials/office-footer'],
        'legal'  => ['subject' => 'business', 'part' => ['legal_name', 'tax_id'], 'label' => 'Fine print'],
    ],
],
```

```php
$formats  = Contenir\Contact\Format\Formats::fromConfig($config['contenir_contact']['format'] ?? []);
$renderer = new Contenir\Contact\Format\HtmlRenderer(Contenir\Contact\MapProvider::fromSettings($settings));

echo $renderer->render($office, $formats->get('footer'));
echo $renderer->render($business, $formats->chosen($block['fields']));   // a Contact block's format
```

`HtmlRenderer` draws an `<address>` with a BEM class per part (`contact__address`, `contact__telephone`…), the
telephone and email as links, every value escaped; a part without a value is left out. For the business, the address
and map are its primary office's. A site's own template for a format is drawn by its framework adapter.

## Development

```bash
composer check             # everything below
composer cs-check          # mago format --check && mago lint
composer static-analysis   # mago analyze
composer test              # unit suite
composer test-integration  # the vocabulary generator, run on a file
composer mutation-test     # Infection
```

## Licence

BSD-3-Clause. See [LICENSE.md](LICENSE.md).
