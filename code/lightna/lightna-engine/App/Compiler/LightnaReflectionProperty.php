<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

class LightnaReflectionProperty
{
    public string $class;
    public string $name;
    public ?string $type;
    public string $visibility;
    public array $doc;
    public bool $isArrayOf;
    public bool $isInterface;
    public bool $isRequired;
    public ?string $arrayItemType;
    public bool $hasLazyDefiner;
}
