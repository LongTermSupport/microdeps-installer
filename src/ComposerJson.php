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
    public const EXCEPTION_MULTIPLE_AUTOLOAD            = 'Found more than one autoload folder, not supported: %s';
    public const EXCEPTION_FAILED_GETTING_FILE_CONTENTS = 'Failed getting contents of file %s';
    public const KEY_AUTOLOAD                           = 'autoload';
    public const KEY_AUTOLOAD_DEV                       = 'autoload-dev';

    public static function fromDirectory(string $pathToDirectory): self
    {
        return self::fromFile($pathToDirectory . '/composer.json');
    }

    public static function fromFile(string $pathToFile): self
    {
        $jsonString = file_get_contents($pathToFile);
        if (false === $jsonString) {
            throw new \InvalidArgumentException('Failed getting contents of file at ' . $pathToFile);
        }

        return new self($jsonString);
    }

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
//        if (count($autoload) > 1) {
//            throw new \InvalidArgumentException(
//                sprintf(self::EXCEPTION_MULTIPLE_AUTOLOAD, print_r($autoload, true))
//            );
//        }
        $namespace = (string)key($autoload);
        $current   = current($autoload);
        $path      = is_string($current) ? $current : current($current);

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