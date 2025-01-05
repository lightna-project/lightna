<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

/**
 * @property-read array attributes
 */
class Block extends DataA
{
    public function attributes(): string
    {
        if (!is_object($this->attributes)) {
            return '';
        }

        $html = '';
        foreach ($this->attributes as $name => $value) {
            $html .= ' ' . $name . '="' . $this->attributeValue($name) . '"';
        }

        return $html;
    }

    public function attributeValue(string $name): string
    {
        $value = $this->attributes->$name ?? '';
        $value = is_array($value) ? implode(' ', $value) : $value;

        return escape($value);
    }
}
