<?php

declare(strict_types=1);

namespace MicroDeps\Installer;

use Composer\Autoload\ClassLoader;
use MicroDeps\Installer\Cli\Args;
use Symfony\Component\Filesystem\Filesystem;

class Main
{
    private string       $vendorDir;
    private ComposerJson $projectComposer;
    private ComposerJson $vendorComposer;
    public const INSTALL_NAMESPACE = 'MicroDeps';
    private string $projectRoot;

    public function __construct(private Args $args, private Filesystem $filesystem, string $overrideProjectRoot = null)
    {
        if (PHP_SAPI !== 'cli') {
            throw new \RuntimeException('This class only works in command line PHP');
        }
        $this->projectRoot = $overrideProjectRoot ?? $this->getProjectRoot();
    }

    public function run(): void
    {
        $this->vendorDir       = $this->getVendorDir();
        $this->projectComposer = ComposerJson::fromDirectory($this->projectRoot);
        $this->vendorComposer  = ComposerJson::fromDirectory($this->vendorDir);
        $this->copySrc();
        $this->copyTest();
    }

    private function copySrc()
    {
        $srcSrcPath  = $this->vendorDir . '/' . $this->vendorComposer->getSrcPath();
        $srcDestPath = $this->projectRoot.'/'.$this->projectComposer->getSrcPath() . '/' . self::INSTALL_NAMESPACE . '/';
        $this->copyDir($srcSrcPath, $srcDestPath);
        $originNamespace = rtrim($this->vendorComposer->getSrcNamespace(), '\\');
        $newNamespace    = rtrim($this->projectComposer->getSrcNamespace() . '\\' . self::INSTALL_NAMESPACE, '\\');
        $this->updateNamespace($originNamespace, $newNamespace, $srcDestPath);
    }

    private function copyTest()
    {
        $testSrcPath  = $this->vendorDir . '/' . $this->vendorComposer->getTestPath();
        $testDestPath = $this->projectRoot.'/'.$this->projectComposer->getTestPath() . '/' . self::INSTALL_NAMESPACE . '/';
        $this->copyDir($testSrcPath, $testDestPath);
        $originNamespace = rtrim($this->vendorComposer->getTestNamespace(), '\\');
        $newNamespace    = rtrim($this->projectComposer->getTestNamespace() . '\\' . self::INSTALL_NAMESPACE, '\\');
        $this->updateNamespace($originNamespace, $newNamespace, $testDestPath);
    }

    private function copyDir(string $src, string $dest): void
    {
        $this->filesystem->exists($dest) || $this->filesystem->mkdir($dest);
        $this->filesystem->mirror($src, $dest);
    }

    private function updateNamespace($originNamespace, $newNamespace, string $dir): void
    {
        $files = glob("$dir/*.php");
        foreach ($files as $filename) {
            $content = \Safe\file_get_contents($filename);
            $updated = str_replace($originNamespace, $newNamespace, $content);
            \Safe\file_put_contents($filename, $updated);
        }
    }

    private function getVendorDir(): string
    {
        $vendorDir = $this->args->getVendorDir();
        if (!is_dir($vendorDir) && !is_dir($vendorDir = $this->getProjectRoot() . $vendorDir)) {
            throw new \InvalidArgumentException('vendor dir does not exist ' . $vendorDir);
        }
        if (!file_exists("$vendorDir/composer.json")) {
            throw new \InvalidArgumentException('vendor dir does not seem valid, no composer json found within ' .
                                                $vendorDir);
        }

        return $vendorDir;
    }

    private function getProjectRoot(): string
    {
        return dirname((new \ReflectionClass(ClassLoader::class))->getFileName(), 3) . '/';
    }
}