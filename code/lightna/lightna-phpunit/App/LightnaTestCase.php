<?php

declare(strict_types=1);

namespace Lightna\PhpUnit\App;

use Exception;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\ObjectManager;

trait LightnaTestCase
{
    /**
     * @template T
     * @param class-string<T> $type
     * @return T
     * @throws Exception|\PHPUnit\Framework\MockObject\Exception
     */
    protected function newSubject(string $type, array $methods = [], array $dependencies = []): object
    {
        if (!$schema = ObjectManager::getClassSchema($type)) {
            throw new LightnaException('Class schema for "' . $type . '" not found');
        }

        /** @var ObjectA $subject */
        $subject = $this->getMockBuilder($type)
            ->onlyMethods(array_keys($methods))
            ->setConstructorArgs([$schema])
            ->getMock();

        foreach ($methods as $method => $return) {
            $subject->method($method)->willReturn($return);
        }

        $deps = [];
        foreach ($dependencies as $property => $value) {
            if (!isset($schema[$property])) {
                throw new LightnaException('Property ' . $property . ' is not a dependency');
            }
            if ($schema[$property][0] === 'o') {
                if (is_object($value)) {
                    $deps[$property] = $value;
                } else {
                    $mock = $this->createMock($schema[$property][1]);
                    foreach ($value as $method => $return) {
                        $mock->method($method)->willReturn($return);
                    }
                    $deps[$property] = $mock;
                }
            } else {
                $deps[$property] = $value;
            }
        }

        return $subject->__mock($deps);
    }
}
