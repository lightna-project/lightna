<?php declare(strict_types=1);

namespace Lightna\Engine\App\HeaderProvider;

interface HeaderProviderDynamicInterface extends HeaderProviderInterface
{
    /**
     * Set the source for the dynamic header
     *
     * @param Dynamic\SourceInterface $source
     *
     * @return void
     */
    public function setSource(Dynamic\SourceInterface $source): void;

    /**
     * Get the source for the dynamic header
     *
     * @return Dynamic\SourceInterface
     */
    public function getSource(): Dynamic\SourceInterface;
}
