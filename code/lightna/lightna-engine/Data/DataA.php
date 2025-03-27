<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use AllowDynamicProperties;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\ObjectA;

#[AllowDynamicProperties]
class DataA extends ObjectA
{
    protected function init(array $data = []): void
    {
        foreach ($this->__objectify($data) as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /** @internal */
    protected function __objectify(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_string($key) && $this->issetProperty($key)) {
                    $this->__setPropertyData($key, $value);
                } elseif (!count($value)) {
                    $result[$key] = [];
                } elseif (is_string(array_key_first($value))) {
                    $result[$key] = newobj(DataA::class, $value);
                } else {
                    $result[$key] = $this->__objectify($value);
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function __defineProperty(string $name): bool
    {
        if (!$prop = $this->__getPropertySchema($name)) {
            return false;
        }

        if ($prop[0] === 'o' && array_key_exists('data', $prop)) {
            $this->$name = newobj($prop[1], $prop['data']);
            return true;
        } elseif ($prop[0] === 'ao') {
            $this->$name = [];
            if (array_key_exists('data', $prop)) {
                foreach ($prop['data'] as $k => $value) {
                    $this->$name[$k] = is_object($value) ? $value : newobj($prop[1], $value);
                }
            }
            return true;
        } else {
            return parent::__defineProperty($name);
        }
    }

    /**
     * Suppress warning when accessing undefined property to lessen "isset" bureaucracy
     * Return by reference makes functions reset|end working for $object->items
     */
    protected function &__get_fallback(string $name): mixed
    {
        $this->$name = null;

        return $this->$name;
    }

    /**
     * Escape method for object needs to be specified strictly (no default value)
     */
    public function __invoke(string $escapeMethod): string
    {
        return escape($this, $escapeMethod);
    }

    /**
     * Escape value on call with escape parameters
     */
    public function __call(string $name, array $arguments)
    {
        if (!isset($this->{$name})) {
            if (!property_exists($this, $name)) {
                throw new LightnaException('Invoking undefined property ' . $this::class . '::' . $name);
            } else {
                return '';
            }
        }

        if (is_scalar($this->{$name}) || is_array($this->{$name})) {
            return escape($this->{$name}, ...$arguments);
        } else {
            // Object, call property __invoke
            return ($this->{$name})(...$arguments);
        }
    }
}
