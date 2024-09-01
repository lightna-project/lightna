<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Project;

use Lightna\Engine\App\DatabaseA;

class Database extends DatabaseA
{
    /** @AppConfig(project/connection) */
    protected array $connection;
}
