<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Trait;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function bin2hex;
use function is_dir;
use function mkdir;
use function random_bytes;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

/**
 * A directory of the test's own, removed with everything in it afterwards.
 */
trait TempDirectoryTrait
{
    private string $directory = '';

    protected function setUpTempDirectory(): void
    {
        $this->directory = sys_get_temp_dir() . '/contenir-contact-' . bin2hex(random_bytes(6));
        mkdir($this->directory);
    }

    protected function tearDownTempDirectory(): void
    {
        if (! is_dir($this->directory)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo) {
                continue;
            }

            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($this->directory);
    }
}
