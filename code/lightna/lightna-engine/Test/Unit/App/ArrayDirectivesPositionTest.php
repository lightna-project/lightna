<?php

declare(strict_types=1);

namespace Lightna\Engine\Test\Unit\App;

class ArrayDirectivesPositionTest extends ArrayDirectivesTestCase
{
    public function testAssocFirstFromTheMiddle(): void
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

    public function testAssocFirstFromTheBeginning(): void
    {
        $this->checkDirectivesNoChange(['position assoc.a first']);
    }

    public function testAssocLast(): void
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

    public function testAssocAfterDc(): void
    {
        $this->checkDirectivesNoChange(['position assoc.d after c']);
    }

    public function testAssocAfterCd(): void
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

    public function testAssocAfterBa(): void
    {
        $this->checkDirectivesNoChange(['position assoc.b after a']);
    }

    public function testAssocAfterCa(): void
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

    public function testAssocBeforeCb(): void
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

    public function testAssocBeforeAb(): void
    {
        $this->checkDirectivesNoChange(['position assoc.a before b']);
    }

    public function testAssocBeforeCd(): void
    {
        $this->checkDirectivesNoChange(['position assoc.c before d']);
    }

    public function testNumericFirst(): void
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

    public function testNumericBefore13(): void
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

    public function testNumericAfter31(): void
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

    public function testNumericFirst0(): void
    {
        $this->checkDirectivesNoChange(['position numeric.0 first']);
    }

    public function testNumericLast3(): void
    {
        $this->checkDirectivesNoChange(['position numeric.3 last']);
    }
}
