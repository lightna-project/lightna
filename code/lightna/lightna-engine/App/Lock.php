<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Project\Database;

class Lock extends ObjectA
{
    protected Database $db;

    public function get(string $name, int $timeout = 0): bool
    {
        return $this->db->getLock($name, $timeout);
    }

    public function release(string $name): void
    {
        $this->db->releaseLock($name);
    }
}
