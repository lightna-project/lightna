<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Flag extends ObjectA
{
    protected Database $db;

    public function get(string $flag): mixed
    {
        $json = $this->db->fetchOneCol($this->getFlagSelect($flag)) ?? '';

        return json_decode($json, true);
    }

    public function set(string $flag, mixed $value): void
    {
        if ($this->isset($flag)) {
            $this->updateFlag($flag, $value);
        } else {
            $this->insertFlag($flag, $value);
        }
    }

    protected function getFlagSelect(string $flag): Select
    {
        return $this->db->select()
            ->from('flag')
            ->columns(['flag_data'])
            ->where(['flag_code = ?' => $flag]);
    }

    protected function isset(string $flag): bool
    {
        return count($this->db->fetch($this->getFlagSelect($flag))) > 0;
    }

    protected function updateFlag(string $flag, mixed $value): void
    {
        $this->db->sql(
            $this->db->update()
                ->table('flag')
                ->where(['flag_code = ?' => $flag])
                ->set(['flag_data' => json($value)])
        );
    }

    protected function insertFlag(string $flag, mixed $value): void
    {
        $this->db->sql(
            $this->db->insert()
                ->into('flag')
                ->values([
                    'flag_code' => $flag,
                    'flag_data' => json($value),
                ])
        );
    }
}
