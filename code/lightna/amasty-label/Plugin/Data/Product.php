<?php

declare(strict_types=1);

namespace Lightna\AmastyLabel\Plugin\Data;

use Closure;
use Lightna\AmastyLabel\Data\Product\AmastyLabel;
use Lightna\Engine\App\ObjectA;

class Product extends ObjectA
{
    public function getDataExtended(Closure $proceed, array $data): array
    {
        $data = $proceed($data);
        $data['amastyLabel'] = newobj(
            AmastyLabel::class,
            $data['amastyLabel'] ?? [],
        );

        return $data;
    }
}
