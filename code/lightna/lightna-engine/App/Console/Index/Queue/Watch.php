<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Queue;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\Query\Index\Changelog;
use Lightna\Engine\App\Query\Index\Queue;

class Watch extends CommandA
{
    protected Indexer $indexer;
    protected Changelog $changelog;
    protected Queue $queue;
    /** @AppConfig(backend:indexer/watch) */
    protected array $options;
    protected int $startTime;

    public function run(): void
    {
        $this->startTime = time();
        while ($this->canProcess()) {
            if ($this->hasWork()) {
                $this->indexer->process();
            }
            sleep($this->options['interval']);
        }
    }

    protected function canProcess(): bool
    {
        $time = time();
        $minute = (int)($time / 60);
        $startMinute = (int)($this->startTime / 60);
        if ($minute !== $startMinute) {
            return false;
        }

        $second = $time % 60;
        $secondsToStart = $this->options['start_at'] - $second;
        if ($secondsToStart > 0) {
            sleep($secondsToStart);
            return $this->canProcess();
        }

        if ($secondsToStart === 0) {
            return true;
        }

        if ($second >= $this->options['stop_at']) {
            return false;
        }

        return true;
    }

    protected function hasWork(): bool
    {
        return !$this->changelog->isEmpty() || !$this->queue->isEmpty() || $this->indexer->getOutdatedEntities();
    }
}
