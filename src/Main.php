<?php

declare(strict_types=1);

namespace MicroDeps\Installer;

use Composer\Autoload\ClassLoader;
use MicroDeps\Installer\Cli\Args;

class Main
{
    public function __construct(private Args $args)
    {
        if (PHP_SAPI !== 'cli') {
            throw new \RuntimeException('This class only works in command line PHP');
        }
    }

    public function run(): void
    {
        $this->assertValidVendorDir();
        $this->assertValidSrcDir();
        $this->assertValidTestsDir();
    }

    private function copy()

    private function assertValidVendorDir(): void
    {
        $vendorDir = $this->args->getVendorDir();
        if (!is_dir($vendorDir)) {
            throw new \InvalidArgumentException('vendor dir does not exist ' . $vendorDir);
        }
        if (!file_exists("$vendorDir/composer.json")) {
            throw new \InvalidArgumentException('vendor dir does not seem valid, no composer json found within ' .
                                                $vendorDir);
        }
    }

    private function getProjectRoot(): string
    {
        return dirname((new \ReflectionClass(ClassLoader::class))->getFileName(), 3);
    }

    private function assertValidSrcDir(): void
    {
        $src           = $this->args->getSrcDir();
        $rootSrcDir    = substr($src, 0, strpos($src, '/'));
        $projectSrcDir = $this->getProjectRoot() . '/' . $rootSrcDir;
        if (!is_dir($projectSrcDir)) {
            throw new \InvalidArgumentException(
                'src dir ' . $src . ' does not seem valid, ' . $projectSrcDir . ' does not exist'
            );
        }
    }

    private function assertValidTestsDir(): void
    {
        $tests         = $this->args->getTestsDir();
        $rootTestsDir  = substr($tests, 0, strpos($tests, '/'));
        $projectSrcDir = $this->getProjectRoot() . '/' . $rootTestsDir;
        if (!is_dir($projectSrcDir)) {
            throw new \InvalidArgumentException(
                'tests dir ' . $tests . ' does not seem valid, ' . $projectSrcDir . ' does not exist'
            );
        }
    }
}