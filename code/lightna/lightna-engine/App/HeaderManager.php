<?php declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\HeaderProvider\HeaderProviderInterface;

class HeaderManager implements ObjectManagerIgnore
{

    /**
     * @var \Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface[]
     */
    private array $headerProviders;

    /**
     * @param HeaderProviderInterface[] $headerProviderList
     *
     * @throws \Exception
     */
    public function __construct(array $headerProviderList = [])
    {
        foreach ($headerProviderList as $header) {
            if (!($header instanceof HeaderProviderInterface)) {
                throw new \RuntimeException('The header provider is invalid. Verify and try again.');
            }
        }
        $this->headerProviders = $headerProviderList;


    }

    public function sendHeaders(): void
    {
        foreach ($this->headerProviders as $provider) {
            if ($provider->canApply()) {
                header($provider->getName() . ': ' . $provider->getValue());
            }
        }
    }
}
