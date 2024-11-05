<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Website extends ObjectA
{
    protected Database $db;
    protected array $websites;

    protected function defineWebsites(): void
    {
        $this->websites = $this->db->fetch(
            $this->db->select('store_website'),
            'website_id',
        );
    }

    public function getList(): array
    {
        return $this->websites;
    }
}
