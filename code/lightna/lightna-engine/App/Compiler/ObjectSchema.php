<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Exception;
use Lightna\Engine\App\Autoloader;
use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\ObjectManager;
use Lightna\Engine\App\ObjectManagerIgnore;
use Lightna\Engine\App\Opcache\Compiled;
use Lightna\Engine\Data\DataA;

class ObjectSchema extends CompilerA implements ObjectManagerIgnore
{
    protected array $config;
    protected array $ignoredClasses = [
        Bootstrap::class => 1,
        Autoloader::class => 1,
        ObjectManager::class => 1,
        LightnaReflectionClass::class => 1,
        LightnaReflectionProperty::class => 1,
    ];
    protected string $moduleClassRx;

    public function make(): void
    {
        $this->init();
        $this->makeObjects();
    }

    protected function init(array $data = []): void
    {
        $this->compiled = new Compiled();
        foreach (LIGHTNA_AREAS as $scope) {
            $this->config[$scope] = $this->compiled->loadAppConfig($scope);
        }
        $moduleNamespaces = array_keys($this->config['backend']['modules'] ?? []);
        $rx = '^(Lightna\\\Engine';
        foreach ($moduleNamespaces as $ns) {
            $rx .= '|' . preg_quote($ns, '~');
        }
        $this->moduleClassRx = "~$rx)~";
    }

    protected function makeObjects(): void
    {
        $classes = $this->compiled->load('object/map');
        $objects = [];
        foreach ($classes as $class => $null) {
            if (!$this->isModuleClass($class)) {
                continue;
            }

            $refClass = new LightnaReflectionClass($class);
            $this->validateRefClass($refClass);
            if (!$this->isRefClassRelevant($refClass)) {
                continue;
            }

            $objects[$class]['p'] = $this->parseClassProperties($refClass);
        }

        $this->compiled->save('object/schema', $objects);
    }

    protected function parseClassProperties(LightnaReflectionClass $refClass): array
    {
        $properties = [];
        foreach ($refClass->getProperties() as $property) {
            if ($property->hasLazyDefiner) {
                // Lazy property with own definer
                $properties[$property->name] = ['l', ''];
            } elseif ($this->isPropertyInjectable($property)) {
                // Object
                $properties[$property->name] = ['o', $property->type];
            } elseif ($numericType = $this->parseNumericType($property)) {
                // Array of objects
                $properties[$property->name] = ['ao', $numericType];
            } elseif ($path = $this->parseAppConfigPath($property)) {
                // Config
                $properties[$property->name] = ['c', $path];
            }

            if (isset($properties[$property->name])) {
                $properties[$property->name][] = $property->visibility;
            }
        }

        return $properties;
    }

    protected function isPropertyInjectable(LightnaReflectionProperty $property): bool
    {
        return $property->type
            && ctype_upper($property->type[0])
            && $property->isRequired
            && !$property->isInterface;
    }

    protected function parseAppConfigPath(LightnaReflectionProperty $property): ?string
    {
        if (!$path = ($property->doc['AppConfig'] ?? null)) {
            return null;
        }

        $ms = explode(':', $path);
        if (count($ms) > 1) {
            $scope = $ms[0];
            $path = $ms[1];
        } else {
            $scope = 'frontend';
            $path = $ms[0];
        }

        if ($property->isRequired && ($this->getConfigValue($scope, $path) === null)) {
            throw new Exception('Config value ' . $path . ' required for ' . $property->class);
        }

        return $path;
    }

    protected function parseNumericType(LightnaReflectionProperty $property): string
    {
        if (!is_a($property->class, DataA::class, true)) {
            return '';
        }

        return $property->isArrayOf ? $property->arrayItemType : '';
    }

    protected function validateRefClass(LightnaReflectionClass $class): void
    {
        if (
            !$class->isInterface()
            && !($this->ignoredClasses[$class->getName()] ?? 0)
            && !is_a($class->getName(), ObjectA::class, true)
            && !is_a($class->getName(), ObjectManagerIgnore::class, true)
        ) {
            throw new Exception(
                'Class "' . $class->getName() . '" needs to extend ' . ObjectA::class
                . ' or implement ' . ObjectManagerIgnore::class . ' interface'
            );
        }
    }

    protected function isModuleClass(string $class): bool
    {
        return (bool)preg_match($this->moduleClassRx, $class);
    }

    protected function isRefClassRelevant(LightnaReflectionClass $class): bool
    {
        return is_a($class->getName(), ObjectA::class, true);
    }

    protected function getConfigValue(string $scope, string $path): mixed
    {
        return array_path_get($this->config[$scope], $path);
    }
}
