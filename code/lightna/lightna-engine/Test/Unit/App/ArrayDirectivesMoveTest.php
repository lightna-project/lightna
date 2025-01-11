<?php

declare(strict_types=1);

namespace Lightna\Engine\Test\Unit\App;

class ArrayDirectivesMoveTest extends ArrayDirectivesTestCase
{
    public function testAssocDx(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'x' => 4,
        ];

        $this->checkDirectives(['move assoc.d to assoc.x'], $expected);
    }

    public function testAssocCx(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'b' => 2,
            'd' => 4,
            'x' => 3,
        ];

        $this->checkDirectives(['move assoc.c to assoc.x'], $expected);
    }

    public function testAssocAff(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'ff' => 1,
        ];

        $this->checkDirectives(['move assoc.a to assoc.ff'], $expected);
    }

    public function testAssocToNumericAff(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ];
        $expected['numeric'] = [
            0 => 'a',
            1 => 'b',
            2 => 'c',
            3 => 'd',
            'ff' => 1,
        ];

        $this->checkDirectives(['move assoc.a to numeric.ff'], $expected);
    }

    public function testAssocRename(): void
    {
        $expected = $this->configSample;
        $expected['assocNew'] = $expected['assoc'];
        unset($expected['assoc']);

        $this->checkDirectives(['move assoc to assocNew'], $expected);
    }

    public function testNumeric010(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            1 => 'b',
            2 => 'c',
            3 => 'd',
            10 => 'a',
        ];

        $this->checkDirectives(['move numeric.0 to numeric.10'], $expected);
    }

    public function testNumeric310(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            0 => 'a',
            1 => 'b',
            2 => 'c',
            10 => 'd',
        ];

        $this->checkDirectives(['move numeric.3 to numeric.10'], $expected);
    }

    public function testNumeric30(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            0 => 'a',
            1 => 'b',
            2 => 'c',
            9 => 'd',
        ];

        $this->checkDirectives(['move numeric.3 to numeric.9'], $expected);
    }

    public function testAssocObject(): void
    {
        $expected = $this->configSample;
        $expected['lv1'] = [
            'lv2_1' => [
                'lv3' => 'lv3_value',
            ],
        ];

        $this->checkDirectives(['move lv1.lv2 to lv1.lv2_1'], $expected);
    }
}
