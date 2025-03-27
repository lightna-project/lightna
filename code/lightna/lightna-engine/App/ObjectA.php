<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use JsonSerializable;
use Lightna\Engine\App\Exception\LightnaException;

class ObjectA implements JsonSerializable
{
    private bool $isInitialized = false;
    private array $properties;

    /**
     * You need to use "protected init" method instead of __construct
     * since object creation is delegated to ObjectManager
     */
    final public function __construct(array $propertiesSchema = [])
    {
        $this->properties = $propertiesSchema;

        // Unset properties to make __get method triggered to define props by ObjectManager
        $this->unsetProperties();
    }

    /** @internal */
    final public function __initialize(array $data = []): void
    {
        if ($this->isInitialized) {
            throw new LightnaException($this::class . ' already initialized');
        }

        $this->init($data);
        $this->isInitialized = true;
    }

    protected function init(array $data = []): void
    {
        // Extension point
    }

    /** @internal */
    final public function __mock(array $dependencies): static
    {
        if (!TEST_MODE) {
            throw new LightnaException('mock method is allowed in TEST_MODE only');
        }

        foreach ($dependencies as $name => $value) {
            if (!$this->__getPropertySchema($name)) {
                throw new LightnaException('Unknown dependency "' . $name . '" for ' . $this::class);
            }
            $this->{$name} = $value;
        }

        return $this;
    }

    public function &__get($name)
    {
        if ($this->__defineProperty($name)) {
            return $this->$name;
        } else {
            return $this->__get_fallback($name);
        }
    }

    /** @internal */
    protected function __defineProperty(string $name): bool
    {
        if ($prop = $this->__getPropertySchema($name)) {
            IS_DEV_MODE && $prop[2] !== 'pb' && $this->__checkAccessibilityInDevMode($name);

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

    final public function issetProperty(string $name): bool
    {
        // True if exists, as it will be defined by OM
        return isset($this->properties[$name]);
    }

    private function unsetProperties(): void
    {
        foreach ($this->properties as $name => $property) {
            unset($this->$name);
        }
    }

    /** @internal */
    protected function __getPropertySchema(string $name): ?array
    {
        return $this->properties[$name] ?? null;
    }

    /** @internal */
    protected function __setPropertyData(string $name, array $data): void
    {
        if ($this->properties[$name]) {
            $this->properties[$name]['data'] = $data;
        }
    }

    protected function &__get_fallback(string $name): mixed
    {
        throw new LightnaException('Attempt to access undefined property ' . $this::class . '::' . $name);
    }

    /** @internal */
    protected function __checkAccessibilityInDevMode(string $name): void
    {
        $caller = null;
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 10);
        foreach ($backtrace as $i => $details) {
            if (($details['function'] ?? null) === '__get') {
                $caller = $backtrace[$i + 1]['object'] ?? null;
                break;
            }
        }

        $inside = $caller && $caller::class === $this::class;
        if (!$inside) {
            throw new LightnaException(
                'Attempt to access protected property ' . $this::class . '::' . $name
                . ' from ' . ($caller ? $caller::class : 'undefined')
            );
        }
    }

    public function __isset(string $name): bool
    {
        return $this->issetProperty($name);
    }

    public function jsonSerialize(): self
    {
        foreach ($this->properties as $name => $property) {
            if ($property[2] === 'pb') {
                // Trigger "define" if undefined
                $this->{$name};
            }
        }

        return $this;
    }
}
