<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Website extends ObjectA
{
    protected Database $db;
    protected array $websites;

    /** @noinspection PhpUnused */
    protected function defineWebsites(): void
    {
        $this->websites = $this->db->fetch(
            $this->getListSelect(),
            'website_id',
        );
    }

    protected function getListSelect(): Select
    {
        return $this->db->select()
            ->from('store_website')
            ->where('website_id > 0');
    }

    public function getList(): array
    {
        return $this->websites;
    }
}
