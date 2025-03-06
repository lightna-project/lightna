<?php declare(strict_types=1);

namespace Lightna\Engine\App\Response\Header;

interface HeaderInterface
{
    public function getName(): string;
    public function getValue(): string;
}
