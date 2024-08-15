<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;

class NotFoundException extends Exception implements ObjectManagerIgnore
{
}
