<?php

declare(strict_types=1);

namespace Lightna\Demo\Plugin;

use Closure;
use Lightna\Engine\Data\Context as ContextData;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\DesignInterface;

class Design
{
    protected ContextData $lightnaContextData;

    public function __construct(
        protected Http $request,
    ) {
        $this->lightnaContextData = getobj(ContextData::class);
    }

    public function aroundSetDefaultDesignTheme(
        DesignInterface $design,
        Closure $proceed
    ): DesignInterface {
        $disabledSemi = !is_null($this->request->getParam('disable_semi'));
        $design->setDesignTheme($disabledSemi ? 4 : 5);

        $this->lightnaContextData->semiDemoMode = $disabledSemi ? 'Magento' : 'Magento + Lightna';

        return $design;
    }
}
