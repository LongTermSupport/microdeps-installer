<?php

declare(strict_types=1);

namespace MicroDeps\Installer;

class ComposerJson
{
    private string $rootDir;
    private string $srcNamespace;
    private string $srcPath;
    private string $testNamespace;
    private string $testPath;

    public function __construct(private string $pathToComposerJson)
    {
        $this->rootDir = dirname($this->pathToComposerJson);
        try {
            $json = json_decode($this->pathToComposerJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException('Failed parsing composer.json, got JsonException', 0, $exception);
        }
        [$this->srcNamespace, $this->srcPath] = $this->parseAutoload($json, 'autoload');
        [$this->testNamespace, $this->testPath] = $this->parseAutoload($json, 'autoload-dev');
    }

    /**
     * @return array{0:string, 1:string}
     */
    private function parseAutoload(array $json, string $key): array
    {
        $autoload =
            $json[$key]['psr-4'] ?? throw new \InvalidArgumentException('Faileding finding psr4 autoload');
        if (count($autoload) > 1) {
            throw new \InvalidArgumentException('Found more than one autoload folder, not supported');
        }
        $namespace = (string)key($autoload);
        $path      = (string)current($autoload);
        if (!is_dir($this->rootDir . '/' . $path)) {
            throw new \InvalidArgumentException('Directory does not exist ' . $path);
        }

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