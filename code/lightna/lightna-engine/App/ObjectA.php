<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use JsonSerializable;

class ObjectA implements JsonSerializable
{
    private bool $isConstructed = false;
    private bool $isInitialized = false;
    private array $properties;

    final public function __construct()
    {
        /**
         * You need to use "protected init" method since object creation is delegated to ObjectManager
         */
    }

    final public function construct(array $properties = []): void
    {
        if ($this->isConstructed) {
            throw new \Exception($this::class . ' already constructed');
        }

        $this->properties = $properties;
        foreach ($this->properties as $name => $property) {
            // Unset properties to make __get method triggered
            unset($this->$name);
        }
        $this->isConstructed = true;
    }

    final public function initialize(array $params = []): void
    {
        if ($this->isInitialized) {
            throw new \Exception($this::class . ' already initialized');
        }

        if (method_exists($this, 'init')) {
            $this->init($params);
        }

        $this->isInitialized = true;
    }

    public function &__get($name)
    {
        if ($this->defineProperty($name)) {
            return $this->$name;
        } else {
            return $this->__get_fallback($name);
        }
    }

    protected function defineProperty(string $name): bool
    {
        if ($prop = $this->getPropertySchema($name)) {
            IS_DEV_MODE && $prop[2] !== 'pb' && $this->checkAccessibilityInDevMode($name);

            if ($prop[0] === 'c') {
                $this->$name = getconf($prop[1]);
            } elseif ($prop[0] === 'l') {
                $this->{"define$name"}();
            } else {
                $this->$name = getobj($prop[1]);
            }

            return true;
        }

        return false;
    }

    protected function issetProperty(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    protected function getPropertySchema(string $name): ?array
    {
        return $this->properties[$name] ?? null;
    }

    protected function setPropertyData(string $name, array $data): void
    {
        if ($this->properties[$name]) {
            $this->properties[$name]['data'] = $data;
        }
    }

    protected function &__get_fallback(string $name): mixed
    {
        throw new \Exception('Attempt to access undefined property ' . $this::class . '::' . $name);
    }

    protected function checkAccessibilityInDevMode(string $name): void
    {
        $inside = ($caller = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3)[2]['object'] ?? null)
            && $caller::class === $this::class;

        if (!$inside) {
            throw new \Exception('Attempt to access property outside ' . $this::class . '::' . $name);
        }
    }

    public function __isset(string $name): bool
    {
        return $this->issetProperty($name);
    }

    public function jsonSerialize(): self
    {
        foreach ($this->properties as $name => $property) {
            $this->defineProperty($name);
        }

        return $this;
    }
}
