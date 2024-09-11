<?php

declare(strict_types=1);

namespace Lightna\Engine\Data\Request;

use Lightna\Engine\Data\DataA;

class Param extends DataA
{
    protected function &__get_fallback(string $name): mixed
    {
        $this->defineParam($name);

        return $this->$name;
    }

    protected function defineParam(string $name): void
    {
        $this->$name = $_REQUEST[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        if (!parent::__isset($name)) {
            return isset($_REQUEST[$name]);
        }

        return true;
    }
}
