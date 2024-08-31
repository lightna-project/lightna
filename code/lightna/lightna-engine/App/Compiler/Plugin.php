<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Lightna\Engine\App\Compiled;
use Lightna\Engine\App\ObjectA;
use ReflectionClass;

class Plugin extends ObjectA
{
    public const EXTENDED_CLASS_SUFFIX = 'Extended';

    protected Compiled $compiled;
    protected array $config;
    protected array $classMap;
    protected array $plugins = [];
    protected array $extended = [];
    protected array $methods;

    public function make(): void
    {
        $this->compiled = new Compiled();
        $this->config = $this->compiled->loadAppConfig();
        $this->classMap = $this->compiled->load('object/map');
        $this->loadPlugins();
        $this->loadMethods();
        $this->applyPlugins();
        $this->applyExtendedConfig();
    }

    protected function loadPlugins(): void
    {
        if (!$plugins = $this->config['plugin'] ?? []) {
            return;
        }

        foreach ($plugins as $className => $pluginClasses) {
            if (!is_array($pluginClasses)) {
                throw new \Exception('Invalid plugin value for '
                    . $className . ':' . var_export($pluginClasses, true));
            }

            $this->validateClass($className);

            foreach ($pluginClasses as $name => $pluginClass) {
                if (!is_string($pluginClass)) {
                    throw new \Exception('Invalid plugin value for '
                        . $className . '.' . $name . ':' . var_export($pluginClass, true));
                }

                $this->validateClass($pluginClass);
                $this->plugins[$className][$name] = $pluginClass;
            }
        }
    }

    protected function loadMethods(): void
    {
        $this->methods = [];
        foreach ($this->plugins as $class => $plugins) {
            $classMethods = $this->getClassMethods($class, ['parseDeclaration' => true, 'parseParams' => true]);
            foreach ($plugins as $plugin) {
                $pluginMethods = $this->getClassMethods($plugin, ['ownMethodsOnly' => true, 'publicOnly' => true]);
                foreach ($pluginMethods as $uname => $method) {
                    $count = 0;
                    $origUname = preg_replace('~extended$~i', '', $uname, -1, $count);
                    if ($count !== 1) {
                        throw new \Exception("Plugin method $plugin::{$method['name']} name must end with \"Extended\"");
                    }

                    if (!isset($classMethods[$origUname])) {
                        throw new \Exception("Plugin method $plugin::{$method['name']} has no available method to plugin in $class");
                    }

                    $methodRef = &$this->methods[$class][$origUname];
                    if (!isset($methodRef)) {
                        $methodRef = $classMethods[$origUname];
                        $methodRef['paramsRef'] = preg_replace('~[$]~m', '&$', $methodRef['params']);
                    }
                    $methodRef['plugins'][] = [
                        'type' => $method['returnClosure'] ? 'c' : 'm',
                        'class' => $plugin,
                    ];
                }
            }

            if (empty($this->methods[$class])) {
                unset($this->plugins[$class]);
            }
        }
    }

    protected function getClassMethods(string $class, array $options): array
    {
        $parseDeclaration = $options['parseDeclaration'] ?? false;
        $publicOnly = $options['publicOnly'] ?? false;
        $ownMethodsOnly = $options['ownMethodsOnly'] ?? false;
        $parseParams = $options['parseParams'] ?? false;

        $classRef = new ReflectionClass($class);
        $methods = [];
        foreach ($classRef->getMethods() as $method) {
            $match = $method->isPublic() || (!$publicOnly && $method->isProtected());
            if ($ownMethodsOnly) {
                $match = $match && $method->getDeclaringClass()->getName() === $class;
            }

            if ($match) {
                $m = [
                    'name' => $method->getName(),
                    'returnVoid' => $method->getReturnType()?->getName() === 'void',
                    'returnClosure' => $method->getReturnType()?->getName() === 'Closure',
                ];
                $parseDeclaration && $m['declaration'] = $this->getMethodDeclaration($method);
                $parseParams && $m['params'] = $this->getMethodParams($method);
                $methods[strtolower($method->getName())] = $m;
            }
        }

        return $methods;
    }

    protected function getMethodDeclaration(\ReflectionMethod $method): string
    {
        static $className, $classContent;

        if ($className !== $method->class) {
            $className = $method->class;
            $classContent = file($method->getFileName());
        }

        $methodSource = implode("", array_slice(
            $classContent,
            $method->getStartLine() - 1,
            $method->getEndLine() - ($method->getStartLine() - 1)
        ));

        if (!preg_match('~^\s*(.+?)\s*\\{\\n~m', $methodSource, $matches)) {
            throw new \Exception('Can\'t parse method declaration for ' . $method->getDeclaringClass() . '::' . $method->getName());
        }

        return $matches[1];
    }

    protected function getMethodParams(\ReflectionMethod $method): string
    {
        $params = '';
        $sep = '';
        foreach ($method->getParameters() as $parameter) {
            $params .= $sep . '$' . $parameter->getName();
            $sep = ', ';
        }

        return $params;
    }

    protected function applyPlugins(): void
    {
        foreach ($this->plugins as $className => $null) {
            $this->applyClassPlugins($className);
        }
    }

    protected function applyClassPlugins(string $class): void
    {
        $extendedClass = $class . static::EXTENDED_CLASS_SUFFIX;
        $file = 'class/' . $this->classToFile($extendedClass);
        $autoloadFile = rtrim($this->config['compiler']['code_dir'], '/') . '/build/' . $file;

        $this->compiled->putFile($file, $this->createExtendDefinition($class));
        $this->classMap[$extendedClass] = $autoloadFile;
        $this->extended[$class] = $extendedClass;
    }

    protected function createExtendDefinition(string $class): string
    {
        $methods = $this->methods[$class] ?? [];
        $className = $this->parseClassName($class);

        $def = "<?php\n\ndeclare(strict_types=1);"
            . "\n\nnamespace {$className['namespace']};\n\nclass "
            . $className['short'] . static::EXTENDED_CLASS_SUFFIX . " extends \\$class\n{";

        $t = '    ';
        $sep = '';
        foreach ($methods as $method) {
            $return = $method['returnVoid'] ? '' : 'return ';

            $def .= "\n$sep$t{$method['declaration']}";
            if (str_contains($method['declaration'], "\n")) {
                $def .= ' {';
            } else {
                $def .= "\n$t{";
            }
            $def .= "\n$t$t\$plugins = [";
            foreach ($method['plugins'] as $plugin) {
                $def .= "\n$t$t{$t}['{$plugin['type']}', \\{$plugin['class']}::class],";
            }
            $def .= "\n$t$t];";
            $params = $method['params'];
            $paramsRef = $method['paramsRef'];
            $paramsAppend = $params ? ', ' . $params : '';
            $paramsRefAppend = $paramsRef ? ', ' . $paramsRef : '';
            $methodNameExtended = $method['name'] . 'Extended';
            $def .= "\n\n" . <<<PROCEED_CODE
        \$proceed = function () use (&\$plugins, &\$proceed$paramsRefAppend) {
            if (!\$callee = array_shift(\$plugins)) {
                {$return}parent::{$method['name']}($params);
            } else {
                \$instance = getobj(\$callee[1]);
                if (\$callee[0] === 'c') {
                    {$return}\$instance->$methodNameExtended()->bindTo(\$this, __CLASS__)(\$proceed$paramsAppend);
                } else {
                    {$return}\$instance->$methodNameExtended(\$proceed$paramsAppend);
                }
            }
        };

        $return\$proceed();
PROCEED_CODE;

            $def .= "\n$t}";
            $sep = "\n";
        }

        $def .= "\n}\n";

        return $def;
    }

    protected function parseClassName(string $class): array
    {
        $parts = explode('\\', $class);
        $name['short'] = array_pop($parts);
        $name['namespace'] = implode('\\', $parts);

        return $name;
    }

    protected function classToFile(string $class): string
    {
        return preg_replace('~\\\\~', '/', $class) . '.php';
    }

    protected function applyExtendedConfig(): void
    {
        $this->compiled->save('object/map', $this->classMap);
        $this->compiled->save('object/extended', $this->extended);
    }

    protected function validateClass(string $class): void
    {
        if ($class[0] === '\\') {
            // Avoid incorrect config merges in advance
            throw new \Exception('Class name shouldn\'t start with "\\", please fix for "' . $class . '"');
        }

        if (!isset($this->classMap[$class])) {
            throw new \Exception('Class ' . $class . ' not found');
        }
    }
}
