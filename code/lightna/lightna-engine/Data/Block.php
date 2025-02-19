<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

class Block extends DataA
{
    public DataA $attributes;

    public function attributes(): string
    {
        if (!isset($this->attributes) || !is_object($this->attributes)) {
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
