<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Search;

interface ClientInterface
{
    function search(string $indexName, array $query);
}
