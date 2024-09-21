<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Router;

use Exception;
use Lightna\Engine\App\ObjectManagerIgnore;

class BypassedException extends Exception implements ObjectManagerIgnore
{
}
