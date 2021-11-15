<?php

declare(strict_types=1);

namespace MicroDeps\Installer\Tests;

use MicroDeps\Installer\ComposerJson;
use PHPUnit\Framework\TestCase;

class ComposerJsonTest extends TestCase
{
    public function provideValid(): \Generator
    {
        yield 'foo/bar' => [
            __DIR__ . '/Assets/vendor/foo/bar/composer.json',
            'Foo\Bar\\',
            __DIR__ . '/Assets/vendor/foo/bar/src',
            'Foo\Bar\Tests\\',
            __DIR__ . '/Assets/vendor/foo/bar/tests',
        ];
    }

    /**
     * @test
     * @dataProvider provideValid
     */
    public function itCanParseValid(
        string $composerJsonPath,
        string $expectedSrcNamespace,
        string $expectedSrcPath,
        string $expectedTestNamespace,
        string $expectedTestPath
    ): void {
        $actual = (new ComposerJson($composerJsonPath));
        self::assertSame($expectedSrcNamespace, $actual->getSrcNamespace());
        self::assertSame($expectedSrcPath, $actual->getSrcPath());
        self::assertSame($expectedTestNamespace, $actual->getTestNamespace());
        self::assertSame($expectedTestPath, $actual->getTestPath());
    }
}
