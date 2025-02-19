<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Plugin\App\Session;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\Index\Session as SessionIndex;

class DataBuilder extends ObjectA
{
    protected SessionIndex $sessionIndex;

    /** @noinspection PhpUnused */
    public function isReindexRequiredExtended(Closure $proceed, array $origData, array $newData): bool
    {
        $hasChange = array_is_fields_changed(
            ['quote_id', 'customer_id', 'customer_group_id'],
            $origData,
            $newData,
        );

        return $proceed() || $hasChange;
    }

    /** @noinspection PhpUnused */
    public function reindexExtended(Closure $proceed, array $data): array
    {
        return merge($proceed(), $this->sessionIndex->getData($data));
    }
}
