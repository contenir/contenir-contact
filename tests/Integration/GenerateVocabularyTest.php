<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Integration;

use Contenir\Contact\Tests\Trait\TempDirectoryTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;
use function escapeshellarg;
use function exec;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function json_encode;
use function str_contains;

use const JSON_THROW_ON_ERROR;
use const PHP_BINARY;

/**
 * bin/generate-vocabulary.php, run on a small schema.org release written to
 * a directory of the test's own.
 */
#[Group('integration')]
final class GenerateVocabularyTest extends TestCase
{
    use TempDirectoryTrait;

    #[Test]
    public function saysHowToRunItWithoutASource(): void
    {
        static::assertSame(1, $this->generate("{$this->directory}/missing.jsonld", '1.0'));
    }

    #[Test]
    public function writesTheOrganisationTypesTheirAncestorsAndThePropertiesDomains(): void
    {
        $source = "{$this->directory}/schemaorg.jsonld";
        $target = "{$this->directory}/Vocabulary.php";
        file_put_contents($source, json_encode([
            '@graph' => [
                ['@id' => 'schema:Thing', '@type' => 'rdfs:Class'],
                [
                    '@id'             => 'schema:Organization',
                    '@type'           => 'rdfs:Class',
                    'rdfs:subClassOf' => ['@id' => 'schema:Thing'],
                ],
                ['@id' => 'schema:Place', '@type' => 'rdfs:Class', 'rdfs:subClassOf' => ['@id' => 'schema:Thing']],
                [
                    '@id'             => 'schema:LocalBusiness',
                    '@type'           => 'rdfs:Class',
                    'rdfs:subClassOf' => [['@id' => 'schema:Organization'], ['@id' => 'schema:Place']],
                ],
                [
                    '@id'             => 'schema:ProfessionalService',
                    '@type'           => 'rdfs:Class',
                    'rdfs:subClassOf' => ['@id' => 'schema:LocalBusiness'],
                    'rdfs:comment'    => 'This type was deprecated.',
                ],
                [
                    '@id'             => 'schema:Draft',
                    '@type'           => 'rdfs:Class',
                    'rdfs:subClassOf' => ['@id' => 'schema:Organization'],
                    'schema:isPartOf' => ['@id' => 'https://pending.schema.org'],
                ],
                ['@id' => 'schema:Event', '@type' => 'rdfs:Class', 'rdfs:subClassOf' => ['@id' => 'schema:Thing']],
                [
                    '@id'                   => 'schema:geo',
                    '@type'                 => 'rdf:Property',
                    'schema:domainIncludes' => ['@id' => 'schema:Place'],
                ],
                [
                    '@id'                   => 'schema:startDate',
                    '@type'                 => 'rdf:Property',
                    'schema:domainIncludes' => ['@id' => 'schema:Event'],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        static::assertSame(0, $this->generate($source, '99.9', $target));

        $code = file_get_contents($target);
        static::assertSame(
            [true, true, true, true, false, false, false],
            [
                str_contains((string) $code, "public const string VERSION = '99.9';"),
                str_contains(
                    (string) $code,
                    "'LocalBusiness' => [\n            'Organization',\n            'Place',\n        ],",
                ),
                str_contains(
                    (string) $code,
                    "public const array DEPRECATED = [\n        'ProfessionalService',\n    ];",
                ),
                str_contains((string) $code, "'geo' => [\n            'Place',\n        ],"),
                str_contains((string) $code, "'Draft'"),
                str_contains((string) $code, "'Event'"),
                str_contains((string) $code, "'startDate'"),
            ],
        );
    }

    protected function setUp(): void
    {
        $this->setUpTempDirectory();
    }

    protected function tearDown(): void
    {
        $this->tearDownTempDirectory();
    }

    private function generate(string ...$arguments): int
    {
        $output = [];
        $status = 0;
        exec(
            escapeshellarg(PHP_BINARY)
                . ' '
                . escapeshellarg(__DIR__ . '/../../bin/generate-vocabulary.php')
                . ' '
                . implode(' ', array_map(escapeshellarg(...), $arguments))
                . ' 2>&1',
            $output,
            $status,
        );

        return $status;
    }
}
