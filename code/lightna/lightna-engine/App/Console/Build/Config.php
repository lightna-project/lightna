<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Build;

use Lightna\Engine\App\Console\Config as ConfigCommand;

/**
 * Logic is same as parent, however it's placed in the "build" namespace thus works with current `building` build
 */
class Config extends ConfigCommand
{
}
