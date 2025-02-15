<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider\Dynamic;

interface SourceInterface
{
    /**
     * Get the name of the header for dynamic headers
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the value of the header for dynamic headers
     *
     * @return string
     */
    public function getValue(): string;

}