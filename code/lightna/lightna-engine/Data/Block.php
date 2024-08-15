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
            $value = is_array($value) ? implode(' ', $value) : $value;
            $html .= ' ' . $name . '="' . escape($value) . '"';
        }

        return $html;
    }
}
