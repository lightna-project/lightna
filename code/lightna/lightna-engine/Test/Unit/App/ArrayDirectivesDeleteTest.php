<?php

declare(strict_types=1);

namespace Lightna\Engine\Test\Unit\App;

class ArrayDirectivesDeleteTest extends ArrayDirectivesTestCase
{
    public function testAssocKeyD(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];

        $this->checkDirectives(['delete key assoc.d'], $expected);
    }

    public function testAssocKeyA(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ];

        $this->checkDirectives(['delete key assoc.a'], $expected);
    }

    public function testAssocKeyC(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'b' => 2,
            'd' => 4,
        ];

        $this->checkDirectives(['delete key assoc.c'], $expected);
    }

    public function testAssocValue2(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'c' => 3,
            'd' => 4,
        ];

        $this->checkDirectives(['delete value assoc 2'], $expected);
    }

    public function testAssocValue1(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ];

        $this->checkDirectives(['delete value assoc 1'], $expected);
    }

    public function testAssocValue4(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];

        $this->checkDirectives(['delete value assoc 4'], $expected);
    }

    public function testNumericKey1(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            0 => 'a',
            2 => 'c',
            3 => 'd',
        ];

        $this->checkDirectives(['delete key numeric.1'], $expected);
    }

    public function testNumericKey0(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            1 => 'b',
            2 => 'c',
            3 => 'd',
        ];

        $this->checkDirectives(['delete key numeric.0'], $expected);
    }

    public function testNumericKey3(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            0 => 'a',
            1 => 'b',
            2 => 'c',
        ];

        $this->checkDirectives(['delete key numeric.3'], $expected);
    }

    public function testNumericValueA(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            1 => 'b',
            2 => 'c',
            3 => 'd',
        ];

        $this->checkDirectives(['delete value numeric a'], $expected);
    }

    public function testNumericValueB(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            0 => 'a',
            2 => 'c',
            3 => 'd',
        ];

        $this->checkDirectives(['delete value numeric b'], $expected);
    }

    public function testNumericValueD(): void
    {
        $expected = $this->configSample;
        $expected['numeric'] = [
            0 => 'a',
            1 => 'b',
            2 => 'c',
        ];

        $this->checkDirectives(['delete value numeric d'], $expected);
    }

    public function testAssocKeyObject(): void
    {
        $expected = $this->configSample;
        $expected['lv1'] = [];

        $this->checkDirectives(['delete key lv1.lv2'], $expected);
    }

    public function testAssocKeyRootObject(): void
    {
        $expected = $this->configSample;
        unset($expected['lv1']);

        $this->checkDirectives(['delete key lv1'], $expected);
    }
}
