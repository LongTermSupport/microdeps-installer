<?php

declare(strict_types=1);

namespace MicroDeps\Installer\Tests;

use MicroDeps\Installer\Cli\Args;
use MicroDeps\Installer\Main;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class MainTest extends TestCase
{
    private const PROJECT_FILES_DIR = __DIR__ . '/Assets/project/';
    private const WORK_DIR          = __DIR__ . '/../var/MainTest/';
    private const WORK_VENDOR_DIR   = self::WORK_DIR . 'vendor/foo/bar';

    private function prepWorkDir(): void
    {
        $filesystem = new Filesystem();
        $filesystem->exists(self::WORK_DIR) && $filesystem->remove(self::WORK_DIR);
        $filesystem->mirror(self::PROJECT_FILES_DIR, self::WORK_DIR);
    }

    /**
     * @test
     */
    public function itCanInstallMicroDepsToProject(): void
    {
        $this->prepWorkDir();
        $main = new Main(
            new Args(['main', Args::ARG_VENDOR_DIR . '=' . self::WORK_VENDOR_DIR]),
            new Filesystem(),
            self::WORK_DIR
        );
        $main->run();
        self::assertDirectoryExists(self::WORK_DIR . '/src/' . Main::INSTALL_NAMESPACE . '/Bar/');
        self::assertDirectoryExists(self::WORK_DIR . '/src/' . Main::INSTALL_NAMESPACE . '/Bar/Baz');
        self::assertDirectoryExists(self::WORK_DIR . '/src/' . Main::INSTALL_NAMESPACE . '/Bar/Baz/Taz');
        self::assertDirectoryExists(self::WORK_DIR . '/tests/' . Main::INSTALL_NAMESPACE . '/Bar/');
        self::assertDirectoryExists(self::WORK_DIR . '/tests/' . Main::INSTALL_NAMESPACE . '/Bar/Baz');
        self::assertDirectoryExists(self::WORK_DIR . '/tests/' . Main::INSTALL_NAMESPACE . '/Bar/Baz/Taz');
        self::assertNamespace(
            self::WORK_DIR . '/src/' . Main::INSTALL_NAMESPACE . '/Bar/Baz/Taz/Waz.php',
            'Test\\Project\\MicroDeps\\Bar\\Baz\\Taz'
        );
        self::assertNamespace(
            self::WORK_DIR . '/src/' . Main::INSTALL_NAMESPACE . '/Bar/Baz/Bop.php',
            'Test\\Project\\MicroDeps\\Bar\\Baz'
        );

    }

    private static function assertNamespace(string $filePath, string $expected): void
    {
        $contents = \Safe\file_get_contents($filePath);
        \Safe\preg_match('%^namespace (.+?);%m', $contents, $matches);
        $actual = $matches[1] ?? self::fail('No namespace match in ' . substr($contents, 0, 100) . '...');
        self::assertSame($expected, $actual);
    }
}