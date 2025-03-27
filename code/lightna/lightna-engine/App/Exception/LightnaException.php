<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Exception;

use Lightna\Engine\App\ObjectManagerIgnore;
use LogicException;

class LightnaException extends LogicException implements ObjectManagerIgnore
{
}
