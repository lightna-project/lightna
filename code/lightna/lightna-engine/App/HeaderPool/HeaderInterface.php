<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderPool;

interface HeaderInterface
{
    /**
     * Whether the header should be attached to the response
     *
     * @return bool
     */
    public function canApply(): bool;

    /**
     * Header name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Header value
     *
     * @return string
     */
    public function getValue(): string;
}
