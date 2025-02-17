<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Search;

interface ClientInterface
{
    function search(string $indexName, array $query);
}
