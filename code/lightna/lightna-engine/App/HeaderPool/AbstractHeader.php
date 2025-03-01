<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderPool;

use Lightna\Engine\App\ObjectA;

/**
 * Class to be used for setting headers with static values
 */
abstract class AbstractHeader extends ObjectA implements HeaderInterface
{
    protected string $headerName = '';
    protected string $headerValue = '';

    /**
     * Whether the header should be attached to the response
     *
     * @return bool
     */
    public function canApply(): bool
    {
        return true;
    }

    /**
     * Get header name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->headerName;
    }

    /**
     * Get header value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->headerValue;
    }
}
