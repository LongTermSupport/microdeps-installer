<?php

declare(strict_types=1);

namespace MicroDeps\Installer;

use Composer\Autoload\ClassLoader;
use FilesystemIterator;
use InvalidArgumentException;
use MicroDeps\Installer\Cli\Args;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use \RecursiveDirectoryIterator;

class Main
{
    private string       $vendorDir;
    private ComposerJson $projectComposer;
    private ComposerJson $vendorComposer;
    public const INSTALL_NAMESPACE = 'MicroDeps';
    private string $projectRoot;
    private string $originSrcNamespace;
    private string $newSrcNamespace;
    private string $originTestNamespace;
    private string $newTestNamespace;

    public function __construct(private Args $args, private Filesystem $filesystem, string $overrideProjectRoot = null)
    {
        if (PHP_SAPI !== 'cli') {
            throw new RuntimeException('This class only works in command line PHP');
        }
        $this->projectRoot = $overrideProjectRoot ?? $this->getProjectRoot();
    }

    public function run(): void
    {
        $this->vendorDir           = $this->getVendorDir();
        $this->projectComposer     = ComposerJson::fromDirectory($this->projectRoot);
        $this->vendorComposer      = ComposerJson::fromDirectory($this->vendorDir);
        $this->originSrcNamespace  = rtrim($this->vendorComposer->getSrcNamespace(), '\\');
        $this->newSrcNamespace     = $this->projectComposer->getSrcNamespace()
                                     . $this->getInstallSubNamespace($this->originSrcNamespace);
        $this->originTestNamespace = rtrim($this->vendorComposer->getTestNamespace(), '\\');
        $this->newTestNamespace    = $this->projectComposer->getTestNamespace()
                                     . $this->getInstallSubNamespace($this->originTestNamespace);
        $this->copySrc();
        $this->copyTest();
    }

    private function getSecondTierNamespace(string $namespace): string
    {
        return explode('\\', $namespace)[1]
               ?? throw new \RuntimeException('Failed getting second tied namespace from ' . $namespace);
    }

    private function getInstallSubNamespace(string $vendorNamespace): string
    {
        return self::INSTALL_NAMESPACE . '\\' . $this->getSecondTierNamespace($vendorNamespace);
    }

    private function getInstallSubPath(string $vendorNamespace): string
    {
        return str_replace('\\', '/', $this->getInstallSubNamespace($vendorNamespace));
    }

    private function copySrc(): void
    {
        $srcFromPath = $this->vendorDir . '/' . $this->vendorComposer->getSrcPath();
        $srcToPath   = $this->projectRoot . '/' . $this->projectComposer->getSrcPath() . '/' .
                       $this->getInstallSubPath($this->originSrcNamespace) . '/';
        $this->copyDir($srcFromPath, $srcToPath);
        $this->updateNamespace($this->originSrcNamespace, $this->newSrcNamespace, $srcToPath);
    }

    private function copyTest(): void
    {
        $vendorNamespace = $this->vendorComposer->getTestNamespace();
        $testFromPath    = $this->vendorDir . '/' . $this->vendorComposer->getTestPath();
        $testToPath      = $this->projectRoot . '/' . $this->projectComposer->getTestPath() . '/' .
                           $this->getInstallSubPath($vendorNamespace) . '/';
        $this->copyDir($testFromPath, $testToPath);
        $this->updateNamespace($this->originTestNamespace, $this->newTestNamespace, $testToPath);
        $this->updateNamespace($this->originSrcNamespace, $this->newSrcNamespace, $testToPath);
    }

    private function copyDir(string $src, string $dest): void
    {
        $this->filesystem->exists($dest) && $this->filesystem->remove($dest);
        $this->filesystem->mkdir($dest);
        $this->filesystem->mirror($src, $dest);
    }

    private function updateNamespace($originNamespace, $newNamespace, string $dir): void
    {
        $originNamespace = trim($originNamespace, '\\');
        $newNamespace    = $this->removeDoubleSlash(trim($newNamespace, '\\'));
        $iterator        = new \RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    FilesystemIterator::KEY_AS_PATHNAME |
                    FilesystemIterator::CURRENT_AS_FILEINFO |
                    FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::SELF_FIRST
            ),
            '%^.+?\.php$%',
            \RegexIterator::GET_MATCH
        );
        /** @var string[] $matches */
        foreach ($iterator as $matches) {
            $filePath = $matches[0] ?? throw new \RuntimeException('Unexpected empty match in ' . __METHOD__);
            $content  = \Safe\file_get_contents($filePath);
            $updated  = str_replace(
                [" $originNamespace", " \\$originNamespace"],
                [" $newNamespace", " \\$newNamespace"],
                $content
            );
            \Safe\file_put_contents($filePath, $updated);
        }
    }

    private function removeDoubleSlash(string $namespace): string
    {
        return preg_replace('%\\\{2,}%', '\\', $namespace);
    }

    private function getVendorDir(): string
    {
        $vendorDir = $this->args->getVendorDir();
        if (!is_dir($vendorDir) && !is_dir($vendorDir = $this->getProjectRoot() . $vendorDir)) {
            throw new InvalidArgumentException('vendor dir does not exist ' . $vendorDir);
        }
        if (!file_exists("$vendorDir/composer.json")) {
            throw new InvalidArgumentException('vendor dir does not seem valid, no composer json found within ' .
                                               $vendorDir);
        }

        return $vendorDir;
    }

    private function getProjectRoot(): string
    {
        return dirname((new ReflectionClass(ClassLoader::class))->getFileName(), 3) . '/';
    }
}