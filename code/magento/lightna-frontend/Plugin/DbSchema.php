<?php

declare(strict_types=1);

namespace Lightna\Frontend\Plugin;

use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaReaderInterface;

/** @noinspection PhpUnused */

class DbSchema
{
    /** @noinspection PhpUnused */
    public function afterReadTables(DbSchemaReaderInterface $subject, array $tables): array
    {
        foreach ($tables as $key => $table) {
            if (str_starts_with($table, 'lightna_')) {
                unset($tables[$key]);
            }
        }

        return $tables;
    }
}
