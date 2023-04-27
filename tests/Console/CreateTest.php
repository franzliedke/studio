<?php

namespace StudioTests\Console;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

class CreateTest extends AbstractConsoleTest
{
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    function testExecute(): void
    {
        $commandTester = $this->executeCommand(
            ['path' => $this->root->url() . '/company/my-package'],
            [
                // package name
                'company/my-package',
                // default namespace (psr-4)
                'Company/MyPackage',
                // set up PhpUnit
                'yes',
                // set up PhpSpec
                'yes',
                // set up TravisCI
                'yes'
            ]
        );

        $this->assertEquals(
            self::getStructure(),
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );
    }

    protected function getCommandFqcn(): string
    {
        return 'create';
    }

    protected static function getStructure(): array
    {
        return [
            'root' => [
                'company' => [
                    'my-package' => [
                        'spec' => [],
                        'src' => [
                            'Example.php' => file_get_contents(__DIR__ . '/stubs/company/my-package/src/Example.php'),
                        ],
                        'tests' => [
                            'ExampleTest.php' => file_get_contents(__DIR__ . '/stubs/company/my-package/tests/ExampleTest.php'),
                        ],
                        '.gitignore' => file_get_contents(__DIR__ . '/stubs/company/my-package/.gitignore'),
                        '.travis.yml' => file_get_contents(__DIR__ . '/stubs/company/my-package/.travis.yml'),
                        'composer.json' => file_get_contents(__DIR__ . '/stubs/company/my-package/composer.json'),
                        'phpspec.yml' => file_get_contents(__DIR__ . '/stubs/company/my-package/phpspec.yml'),
                        'phpunit.xml' => file_get_contents(__DIR__ . '/stubs/company/my-package/phpunit.xml'),
                    ],
                ],
            ],
        ];
    }
}