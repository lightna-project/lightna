<?php

declare(strict_types=1);

namespace Lightna\Engine\Test\Unit\App;

class ArrayDirectivesReplaceTest extends ArrayDirectivesTestCase
{
    public function testAssoc17(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => '7',
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ];

        $this->checkDirectives(['replace value 1 to 7 in assoc'], $expected);
    }

    public function testAssoc47(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => '7',
        ];

        $this->checkDirectives(['replace value 4 to 7 in assoc'], $expected);
    }

    public function testAssoc37(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'b' => 2,
            'c' => '7',
            'd' => 4,
        ];

        $this->checkDirectives(['replace value 3 to 7 in assoc'], $expected);
    }

    public function testAssoc33(): void
    {
        $expected = $this->configSample;
        $expected['assoc'] = [
            'a' => 1,
            'b' => 2,
            'c' => '3',
            'd' => 4,
        ];

        $this->checkDirectives(['replace value 3 to 3 in assoc'], $expected);
    }
}
