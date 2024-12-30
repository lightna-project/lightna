<?php

declare(strict_types=1);

namespace Lightna\PhpUnit\App;

use Exception;
use Lightna\Engine\App\ObjectA;

trait LightnaTestCase
{
    /**
     * @template T
     * @param class-string<T> $type
     * @return T
     * @throws Exception|\PHPUnit\Framework\MockObject\Exception
     */
    protected function newSubject(string $type, array $mockDependencies = []): object
    {
        /** @var ObjectA $subject */
        $subject = newobj($type);

        $deps = [];
        foreach ($mockDependencies as $property => $value) {
            if (!$schema = $subject->getPropertySchema($property)) {
                throw new Exception('Property ' . $property . ' is not a dependency');
            }
            if ($schema[0] != 'o' || !is_array($value)) {
                $deps[$property] = $value;
            } else {
                $mock = $this->createMock($schema[1]);
                foreach ($value as $method => $return) {
                    $mock->method($method)->willReturn($return);
                }
                $deps[$property] = $mock;
            }
        }

        return $subject->mock($deps);
    }
}
