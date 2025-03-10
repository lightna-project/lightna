<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Project\Database;

use Lightna\Engine\App\Database\Doctrine\AbstractSchemaUpdater;

class SchemaUpdater extends AbstractSchemaUpdater
{
    /** @AppConfig(project/connection) */
    protected array $databaseConnectionParams;
}
