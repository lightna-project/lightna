<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Changelog;

interface CollectorInterface
{
    public function collect(string $table, array $changelog): array;
}
