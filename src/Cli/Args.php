<?php

declare(strict_types=1);

namespace MicroDeps\Installer\Cli;

class Args
{
    private const EQUALS = '=';

    #[UsageAttribute(
        'The relative path (from project root) to the library you want to install, for example vendor/lts/microdeps-pdo'
    )]
    public const  ARG_VENDOR_DIR = 'vendorDir';

    private const MIN_ARGS = 1;
    private const MAX_ARGS = 1;

    private string $vendorDir;

       public function __construct(private array $argv)
    {
        if (PHP_SAPI !== 'cli') {
            throw new \RuntimeException('This class only works in command line PHP');
        }
        $this->parse();
    }

    private function parse(): void
    {
        $args = count($this->argv) - 1;
        if ($args < self::MIN_ARGS || $args > self::MAX_ARGS) {
            $this->usage();
        }
        foreach ($this->argv as $arg) {
            if (!str_contains($arg, self::EQUALS)) {
                continue;
            }
            [$argKey, $argVal] = explode(self::EQUALS, $arg);
            match ($argKey) {
                self::ARG_VENDOR_DIR => $this->vendorDir = $argVal,
                default => throw new \InvalidArgumentException('Invalid argument ' . $argKey)
            };
        }
    }

    private function usage(): void
    {
        printf("\nUsage:\n %s with the following arguments:\n\n", $this->argv[0]);
        /** @var \ReflectionClassConstant[] $consts */
        $consts = (new \ReflectionClass($this))->getConstants(\ReflectionClassConstant::IS_PUBLIC);
        foreach ($consts as $const) {
            $attribs = $const->getAttributes(UsageAttribute::class);
            if ([] === $attribs) {
                continue;
            }
            foreach ($attribs as $attrib) {
                /** @var UsageAttribute $usage */
                $usage = $attrib->newInstance();
                printf("\n%s\n\t1%s\n\t%s\n", $const->getValue(), $usage->getUsage(), $usage->getDefaultValue());
            }
        }
    }

    public function getVendorDir(): string
    {
        if (!is_string($this->vendorDir)) {
            throw new \RuntimeException('vendor dir has not been passed, this is a required argument');
        }

        return $this->vendorDir;
    }

    public function getSrcDir(): string
    {
        return $this->srcDir;
    }

    public function getTestsDir(): string
    {
        return $this->testsDir;
    }
}