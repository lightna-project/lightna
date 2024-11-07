<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Storage\Database;

use Lightna\Engine\App\DatabaseA;

class Client extends DatabaseA
{
    /** @AppConfig(storage/database/options) */
    protected array $connection;
}
