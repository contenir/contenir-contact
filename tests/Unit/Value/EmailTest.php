<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Value;

use Contenir\Contact\Value\Email;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class EmailTest extends TestCase
{
    #[Test]
    public function leavesOutAnythingElse(): void
    {
        static::assertSame([null, null, null], [Email::from('studio'), Email::from(''), Email::from(['a@b.test'])]);
    }

    #[Test]
    public function readsAValidAddress(): void
    {
        $email = Email::from(' studio@example.test ');

        static::assertSame(
            ['studio@example.test', 'mailto:studio@example.test', 'studio@example.test'],
            [$email?->address, $email?->uri(), (string) $email],
        );
    }
}
