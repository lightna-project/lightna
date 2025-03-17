<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use AllowDynamicProperties;
use Exception;
use Lightna\Engine\App\ObjectA;

#[AllowDynamicProperties]
class DataA extends ObjectA
{
    protected function init(array $data = []): void
    {
        foreach ($this->objectify($data) as $key => $value) {
            $this->{$key} = $value;
        }
    }

    protected function objectify(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_string($key) && $this->issetProperty($key)) {
                    $this->setPropertyData($key, $value);
                } elseif (!count($value)) {
                    $result[$key] = [];
                } elseif (is_string(array_key_first($value))) {
                    $result[$key] = newobj(DataA::class, $value);
                } else {
                    $result[$key] = $this->objectify($value);
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function defineProperty(string $name): bool
    {
        if (!$prop = $this->getPropertySchema($name)) {
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
            return parent::defineProperty($name);
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
            throw new Exception('Invoking undefined property or method ' . $this::class . '::' . $name);
        }
        if (is_scalar($this->{$name}) || is_array($this->{$name})) {
            return escape($this->{$name}, ...$arguments);
        } else {
            // Call property __invoke
            return ($this->{$name})(...$arguments);
        }
    }
}
