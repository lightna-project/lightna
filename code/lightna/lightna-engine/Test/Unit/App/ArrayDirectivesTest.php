<?php

declare(strict_types=1);

namespace Lightna\Engine\Test\Unit\App;

use Lightna\Engine\App\ArrayDirectives;
use PHPUnit\Framework\TestCase;

class ArrayDirectivesTest extends TestCase
{
    protected array $configSample = [
        'numeric' => ['a', 'b', 'c', 'd'],
        'assoc' => [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ],
    ];

    protected function checkDirectives(array $directives, array $expected): void
    {
        $config = $this->configSample;
        $config['directives'] = $directives;

        ArrayDirectives::apply($config);
        unset($config['directives']);

        $this->assertSame($expected, $config);
    }

    protected function checkDirectivesNoChange(array $directives): void
    {
        $this->checkDirectives($directives, $this->configSample);
    }

    public function testPositionAssocFirstFromTheMiddle(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'd' => 4,
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];

        $this->checkDirectives(['position assoc.d first'], $expected);
    }

    public function testPositionAssocFirstFromTheBeginning(): void
    {
        $this->checkDirectivesNoChange(['position assoc.a first']);
    }

    public function testPositionAssocLast(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'a' => 1,
        ];

        $this->checkDirectives(['position assoc.a last'], $expected);
    }

    public function testPositionAssocAfterDc(): void
    {
        $this->checkDirectivesNoChange(['position assoc.d after c']);
    }

    public function testPositionAssocAfterCd(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'b' => 2,
            'd' => 4,
            'c' => 3,
        ];

        $this->checkDirectives(['position assoc.c after d'], $expected);
    }

    public function testPositionAssocAfterBa(): void
    {
        $this->checkDirectivesNoChange(['position assoc.b after a']);
    }

    public function testPositionAssocAfterCa(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'c' => 3,
            'b' => 2,
            'd' => 4,
        ];

        $this->checkDirectives(['position assoc.c after a'], $expected);
    }

    public function testPositionAssocBeforeCb(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'c' => 3,
            'b' => 2,
            'd' => 4,
        ];

        $this->checkDirectives(['position assoc.c before b'], $expected);
    }

    public function testPositionAssocBeforeAb(): void
    {
        $this->checkDirectivesNoChange(['position assoc.a before b']);
    }

    public function testPositionAssocBeforeCd(): void
    {
        $this->checkDirectivesNoChange(['position assoc.c before d']);
    }

    public function testPositionNumericFirst(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            3 => 'd',
            0 => 'a',
            1 => 'b',
            2 => 'c',
        ];

        $this->checkDirectives(['position numeric.3 first'], $expected);
    }

    public function testPositionNumericBefore13(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            0 => 'a',
            2 => 'c',
            1 => 'b',
            3 => 'd',
        ];

        $this->checkDirectives(['position numeric.1 before 3'], $expected);
    }

    public function testPositionNumericAfter31(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            0 => 'a',
            1 => 'b',
            3 => 'd',
            2 => 'c',
        ];

        $this->checkDirectives(['position numeric.3 after 1'], $expected);
    }
}
