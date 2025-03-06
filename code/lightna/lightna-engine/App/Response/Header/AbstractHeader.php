<?php declare(strict_types=1);

namespace Lightna\Engine\App\Response\Header;

use Lightna\Engine\App\ObjectA;

abstract class AbstractHeader extends ObjectA implements HeaderInterface
{
    protected string $name;
    protected string $value;

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
