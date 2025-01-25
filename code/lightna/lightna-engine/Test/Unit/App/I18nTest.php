<?php

declare(strict_types=1);

namespace Lightna\Engine\Test\Unit\App;

use Lightna\Engine\App\I18n;
use Lightna\PhpUnit\App\LightnaTestCase;
use PHPUnit\Framework\TestCase;

class I18nTest extends TestCase
{
    use LightnaTestCase;

    public const DATA = [
        'translates' => [
            'simple test' => ['k', 'easy test'],
            'k %1 k %2' => ['k', 'key %1 key %2'],
            'k %a k %b' => ['k', 'key %a key %b'],
            'The total cost is {0, number, currency}' => ['m', 'The total cost is {0, number, currency}'],
            'Use {{variable}}' => ['k', 'Use {{variable}} to test'],
            'user %name login at %login_at' => ['k', 'User %name login at %login_at'],
            'discount %1%' => ['k', 'discount %1%'],
            '%1%1%2%2' => ['k', '%1%1%2%2'],
            '{0}{1}{0}{1}' => ['m', '{0}{1}{0}{1}'],
        ],
        'results' => [
            'easy test' => ['simple test', []],
            'simple test 2' => ['simple test 2', []], // Not exising translate
            'key A key B' => ['k %1 k %2', ['A', 'B']],
            'key A2 key B2' => ['k %a k %b', ['a' => 'A2', 'b' => 'B2']],
            'The total cost is $12.13' => ['The total cost is {0, number, currency}', [12.133]],
            'Use {{variable}} to test' => ['Use {{variable}}', []],
            'User %name login at %login_at' => ['user %name login at %login_at', []],
            'User test login at 11.11.11' => ['user %name login at %login_at', ['name' => 'test', 'login_at' => '11.11.11']],
            'discount 30%' => ['discount %1%', [30]],
            'aabb' => ['%1%1%2%2', ['a', 'b']],
            'abab' => ['{0}{1}{0}{1}', ['a', 'b']],
        ],
    ];

    public function test(): void
    {
        $i18n = $this->newSubject(
            I18n::class,
            ['getTranslates' => static::DATA['translates']],
        );

        foreach (static::DATA['results'] as $result => $phrase) {
            $this->assertEquals(
                $result,
                $i18n->phrase($phrase[0], $phrase[1]),
            );
        }
    }
}
