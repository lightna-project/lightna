<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderPool;

interface HeaderDynamicInterface extends HeaderInterface
{
    /**
     * Get the source for the dynamic header
     *
     * @return Dynamic\SourceInterface
     */
    public function getSource(): Dynamic\SourceInterface;
}
