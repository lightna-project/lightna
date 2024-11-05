<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Compiler;

use Lightna\Engine\App\Compiler\CompilerA;
use Lightna\Engine\App\Opcache\Compiled;
use Lightna\Magento\App\Query\Store;
use Lightna\Magento\App\Query\Website;

class RunCodes extends CompilerA
{
    protected Compiled $compiled;
    protected Store $store;
    protected Website $website;

    public function make(): void
    {
        $this->compiled->save('magento/runCodes', $this->getRunCodes());
    }

    public function getRunCodes(): array
    {
        $stores = $this->store->getList();
        $websites = $this->website->getList();
        $runCodes = [];
        foreach ($stores as $store) {
            if (!$store['is_active']) {
                continue;
            }
            $runCodes['store'][$store['code']] = $store['store_id'];
            $website = $websites[$store['website_id']];
            $runCodes['website'][$website['code']] ??= $store['store_id'];
        }

        return $runCodes;
    }
}
