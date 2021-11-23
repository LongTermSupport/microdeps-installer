<?php

declare(strict_types=1);

namespace MicroDeps\Installer\Tests;

use MicroDeps\Installer\ComposerJson;
use PHPUnit\Framework\TestCase;

class ComposerJsonTest extends TestCase
{
    public function provideValidFiles(): \Generator
    {
        yield 'foo/bar from file' => [
            ComposerJson::createFromFile(__DIR__ . '/Assets/vendor/foo/bar/composer.json'),
            'Foo\Bar\\',
            'src/',
            'Foo\Bar\Tests\\',
            'tests/',
        ];
        yield 'Fancy/Pants from string same namespace src and test' => [
            new ComposerJson(<<<'JSON'
                {
                  "autoload": {
                    "psr-4": {
                      "Fancy\\Pants\\": [
                        "fancy/"
                      ]
                    }
                  },
                  "autoload-dev": {
                    "psr-4": {
                      "Fancy\\Pants\\": [
                        "pants/"
                      ]
                    }
                  }
                }
                JSON
            ),
            'Fancy\Pants\\',
            'fancy/',
            'Fancy\Pants\\',
            'pants/',
        ];
    }

    /**
     * @test
     * @dataProvider provideValidFiles
     */
    public function itCanParseValid(
        ComposerJson $actual,
        string       $expectedSrcNamespace,
        string       $expectedSrcPath,
        string       $expectedTestNamespace,
        string       $expectedTestPath
    ): void {
        self::assertSame($expectedSrcNamespace, $actual->getSrcNamespace());
        self::assertSame($expectedSrcPath, $actual->getSrcPath());
        self::assertSame($expectedTestNamespace, $actual->getTestNamespace());
        self::assertSame($expectedTestPath, $actual->getTestPath());
    }

    /** @test */
    public function itThrowsExpectionOnInvalidJson(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(ComposerJson::EXCEPTION_FAILED_PARSING_JSON, 'Syntax error'));
        new ComposerJson('{foo:}');
    }

    public function provideMissingAutoload(): \Generator
    {
        yield 'typo autoload' => [
            <<<'JSON'
                {
                  "autoloadddd": {
                    "psr-4": {
                      "Fancy\\Pants\\": [
                        "fancy/"
                      ]
                    }
                  },
                  "autoload-dev": {
                    "psr-4": {
                      "Fancy\\Pants\\": [
                        "pants/"
                      ]
                    }
                  }
                }
                JSON,
            ComposerJson::KEY_AUTOLOAD,
        ];
        yield 'just not there at all' => ['{}', ComposerJson::KEY_AUTOLOAD];
        yield 'missing dev autoload' => [
            <<<'JSON'
                {
                  "autoload": {
                    "psr-4": {
                      "Fancy\\Pants\\": [
                        "fancy/"
                      ]
                    }
                  },
                  "autoload-devvvvvvvvvvvv": {
                    "prs-4": {
                      "Fancy\\Pants\\": [
                        "pants/"
                      ]
                    }
                  }
                }
                JSON,
            ComposerJson::KEY_AUTOLOAD_DEV,
        ];
    }

    /**
     * @dataProvider provideMissingAutoload
     * @test
     */
    public function itThrowsExpectionOnMissingAutoload(string $json, string $key): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(ComposerJson::EXCEPTION_FAILED_FINDING_AUTOLOAD, $key));
        new ComposerJson($json);
    }
}
