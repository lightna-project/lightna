<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Storage\Database;

use Lightna\Engine\App\Database\Doctrine\AbstractSchemaUpdater;

class SchemaUpdater extends AbstractSchemaUpdater
{
    /** @AppConfig(storage/database/options) */
    protected array $databaseConnectionParams;
}
