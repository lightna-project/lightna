<?php

declare(strict_types=1);

namespace Lightna\Engine\Test\Unit\App;

use Lightna\Engine\App\ArrayDirectives;
use PHPUnit\Framework\TestCase;

class ArrayDirectivesTestCase extends TestCase
{
    protected array $configSample = [
        'numeric' => ['a', 'b', 'c', 'd'],
        'assoc' => [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ],
        'lv1' => [
            'lv2' => [
                'lv3' => 'lv3_value',
            ],
        ],
    ];

    protected function checkDirectives(array $directives, array $expected): void
    {
        $config = $this->configSample;
        $config['directive'] = $directives;

        ArrayDirectives::apply($config);
        unset($config['directive']);

        $this->assertSame($expected, $config);
    }

    protected function checkDirectivesNoChange(array $directives): void
    {
        $this->checkDirectives($directives, $this->configSample);
    }
}
