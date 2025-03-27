<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Exception;

use Exception;
use Lightna\Engine\App\ObjectManagerIgnore;

class NotFoundException extends Exception implements ObjectManagerIgnore
{
}
