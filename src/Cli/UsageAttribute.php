<?php

declare(strict_types=1);

namespace MicroDeps\Installer\Cli;

#[\Attribute]
class UsageAttribute
{
    public function __construct(private string $usage, private ?string $defaultValue = null)
    {

    }

    public function getUsage(): string
    {
        return $this->usage;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue ?? 'no default value, you must pass in this argument';
    }
}