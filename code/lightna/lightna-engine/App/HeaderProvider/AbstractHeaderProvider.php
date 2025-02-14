<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider;

use Lightna\Engine\App\ObjectManagerIgnore;

/**
 * Class to be used for setting headers with static values
 */
abstract class AbstractHeaderProvider implements HeaderProviderInterface, ObjectManagerIgnore
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
