<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Indexer;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Index\Changelog\Schema as ChangelogSchema;
use Lightna\Engine\App\Index\Queue\Schema as QueueSchema;
use Lightna\Engine\App\Index\Triggers\Schema as TriggersSchema;

class UpdateSchema extends CommandA
{
    protected ChangelogSchema $changelogSchema;
    protected TriggersSchema $triggersSchema;
    protected QueueSchema $queueSchema;

    public function run(): void
    {
        $this->changelogSchema->update();
        $this->triggersSchema->update();
        $this->queueSchema->update();
    }
}
