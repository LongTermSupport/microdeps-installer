<?php

declare(strict_types=1);

namespace MicroDeps\Installer;

class ComposerJson
{
    private string $srcNamespace;
    private string $srcPath;
    private string $testNamespace;
    private string $testPath;
    private array  $json;
    public const EXCEPTION_FAILED_PARSING_JSON          = 'Failed parsing composer.json, got JsonException %s';
    public const EXCEPTION_FAILED_FINDING_AUTOLOAD      = 'Failed finding psr4 %s';
    public const EXCEPTION_MULTIPLE_AUTOLOAD            = 'Found more than one autoload folder, not supported';
    public const EXCEPTION_FAILED_GETTING_FILE_CONTENTS = 'Failed getting contents of file %s';
    public const KEY_AUTOLOAD                           = 'autoload';
    public const KEY_AUTOLOAD_DEV                       = 'autoload-dev';

    public function __construct(private string $composerJson)
    {
        try {
            $this->json = json_decode($this->composerJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException(
                sprintf(self::EXCEPTION_FAILED_PARSING_JSON, $exception->getMessage()),
                0,
                $exception
            );
        }
        [$this->srcNamespace, $this->srcPath] = $this->parseAutoload(self::KEY_AUTOLOAD);
        [$this->testNamespace, $this->testPath] = $this->parseAutoload(self::KEY_AUTOLOAD_DEV);
    }

    public static function createFromFile(string $pathToComposerJson): self
    {
        $composerJson = file_get_contents($pathToComposerJson);
        if (false === $composerJson) {
            throw new \InvalidArgumentException(
                sprintf(self::EXCEPTION_FAILED_GETTING_FILE_CONTENTS, $pathToComposerJson)
            );
        }

        return new self($composerJson);
    }

    /**
     * @return array{0:string, 1:string}
     */
    private function parseAutoload(string $key): array
    {
        $autoload = $this->json[$key]['psr-4'] ?? throw new \InvalidArgumentException(
                sprintf(self::EXCEPTION_FAILED_FINDING_AUTOLOAD, $key)
            );
        if (count($autoload) > 1) {
            throw new \InvalidArgumentException(self::EXCEPTION_MULTIPLE_AUTOLOAD);
        }
        $namespace = (string)key($autoload);
        $path      = (string)current(current($autoload));

        return [$namespace, $path];
    }

    public function getSrcNamespace(): string
    {
        return $this->srcNamespace;
    }

    public function getSrcPath(): string
    {
        return $this->srcPath;
    }

    public function getTestNamespace(): string
    {
        return $this->testNamespace;
    }

    public function getTestPath(): string
    {
        return $this->testPath;
    }
}