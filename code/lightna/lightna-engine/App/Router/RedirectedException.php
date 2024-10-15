<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Router;

use Exception;
use Lightna\Engine\App\ObjectManagerIgnore;

class RedirectedException extends Exception implements ObjectManagerIgnore
{
}
