<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

class LightnaReflectionClass
{
    protected ReflectionClass $reflectionClass;
    /** @var LightnaReflectionProperty[] */
    protected ?array $properties = null;
    protected ?array $currPropertyDoc = null;
    protected ?array $doc = null;
    protected ?array $uses = null;

    public function __construct(
        protected string $name
    ) {
        $this->reflectionClass = new ReflectionClass($this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isInterface(): bool
    {
        return $this->reflectionClass->isInterface();
    }

    public function isTrait(): bool
    {
        return $this->reflectionClass->isTrait();
    }

    public function getProperties(): array
    {
        if ($this->properties !== null) {
            return $this->properties;
        }

        $this->parseProperties();

        return $this->properties;
    }

    protected function parseProperties(): void
    {
        $this->properties = [];
        $methods = $this->getMethods();

        foreach ($this->reflectionClass->getProperties() as $property) {
            if (!$property->getType()) {
                throw new Exception('Property ' . $property->getDeclaringClass()->getName()
                    . '::' . $property->getName() . ' must have a type. If there is no specific type, use "mixed".');
            }

            $this->properties[$property->getName()] = a2o(
                [
                    'doc' => $this->getPropertyDoc($property),
                    'name' => $property->getName(),
                    'class' => $this->getName(),
                    'visibility' => $property->isPublic() ? 'pb' : ($property->isProtected() ? 'pt' : ($property->isPrivate() ? 'pv' : null)),
                    'hasLazyDefiner' => isset($methods['define' . strtolower($property->getName())]),
                ]
                + $this->getPropertyTypeInfo($property),
                LightnaReflectionProperty::class
            );
        }
    }

    protected function getMethods(): array
    {
        $methods = [];
        foreach ($this->reflectionClass->getMethods() as $method) {
            $methods[strtolower($method->getName())] = $method;
        }

        return $methods;
    }

    protected function getPropertyTypeInfo(ReflectionProperty $property): array
    {
        $type = $this->getPropertyDocType()
            ?? $this->getPropertyDocTypeInClass($property)
            ?? $this->getPropertyType($property);

        $type = $this->parsePropertyTypeString($type);

        $isArrayOf = false;
        $arrayItemType = null;
        if ($type && str_ends_with($type, '[]')) {
            $arrayItemType = substr($type, 0, -2);
            $type = 'array';
            $isArrayOf = true;
        }

        $isInterface = $type
            && ctype_upper($type[0])
            && class_exists($type)
            && (new ReflectionClass($type))->isInterface();

        $isRequired = !$property->getType()->allowsNull();

        return compact(['type', 'isRequired', 'isArrayOf', 'arrayItemType', 'isInterface']);
    }

    protected function getPropertyType(ReflectionProperty $property): ?string
    {
        if (!$property->hasType()) {
            return null;
        }

        return (string)$property->getType();
    }

    protected function getPropertyDocType(): ?string
    {
        if (!$docVar = ($this->currPropertyDoc['var'] ?? null)) {
            return null;
        }

        return $docVar;
    }

    protected function getPropertyDocTypeInClass(ReflectionProperty $property): ?string
    {
        if (
            !($doc = $this->getDoc())
            || !($type = ($doc['property'][$property->getName()] ?? null))
        ) {
            return null;
        }

        return $type;
    }

    protected function parsePropertyTypeString(string $type): ?string
    {
        $type = ltrim($type, '?');
        if (ctype_upper($type[0])) {
            if (str_ends_with($type, '[]')) {
                return $this->resolveClassName(rtrim($type, '[]')) . '[]';
            } else {
                return $this->resolveClassName($type);
            }
        }

        return $type;
    }

    protected function resolveClassName($name): string
    {
        if (!str_contains($name, '\\')) {
            $uses = $this->getUses();
            if (!isset($uses[$name])) {
                // Use current namespace
                return $this->reflectionClass->getNamespaceName() . '\\' . $name;
            } else {
                return $uses[$name];
            }
        }

        return $name;
    }

    public function getDoc(): array
    {
        if ($this->doc !== null) {
            return $this->doc;
        }

        $this->parseDoc();

        return $this->doc;
    }

    protected function parseDoc(): void
    {
        $this->doc = [];
        foreach (explode("\n", (string)$this->reflectionClass->getDocComment()) as $line) {
            if (!str_contains($line, '@')) {
                continue;
            }
            $line = trim(preg_replace(['~^.*@|-read|-write|[()$]~', '~\s+~'], ' ', $line));
            $words = explode(' ', $line);
            if ($words[0] === 'property') {
                $this->doc[$words[0]][$words[2]] = $words[1];
            } else {
                $this->doc[array_shift($words)] = $words[0];
            }
        }
    }

    protected function getPropertyDoc(ReflectionProperty $property): array
    {
        if (!$doc = $property->getDocComment()) {
            return [];
        }

        $docs = [];
        foreach (explode("\n", $doc) as $line) {
            $line = trim(preg_replace(['~^.*@~', '~[()]~', '~\s+~'], ' ', $line));
            $words = explode(' ', $line);
            $docs[array_shift($words)] = $words[0];
        }

        return $docs;
    }

    public function getUses(): array
    {
        if ($this->uses !== null) {
            return $this->uses;
        }

        $this->parseUses();

        return $this->uses;

    }

    public function parseUses(): void
    {
        $this->uses = [];
        $uses[] = $this->getName();

        $content = file_get_contents($this->reflectionClass->getFileName());
        if (!preg_match('~(.+?)\\n(abstract\s*)?class\s~ism', $content, $ms)) {
            return;
        }
        $head = $ms[1];

        foreach (explode("\n", $head) as $line) {
            $ms = null;
            if (preg_match('~^\s*use\s+([^\s]+)(\s+as ([^\s]+))?\s*;~', $line, $ms)) {
                $i = $ms[3] ?? count($uses);
                $uses[$i] = ltrim($ms[1], '\\');
            }
        }

        foreach ($uses as $as => $use) {
            if (is_int($as)) {
                $parts = explode('\\', $use);
                $as = end($parts);
            }
            $this->uses[$as] = $use;
        }
    }

    public static function getMethodDeclaration(ReflectionMethod $method): string
    {
        $dcl = '';
        $method->isPublic() && $dcl .= 'public ';
        $method->isProtected() && $dcl .= 'protected ';
        $method->isPrivate() && $dcl .= 'private ';
        $method->isStatic() && $dcl .= 'static ';
        $dcl .= 'function ' . $method->getName() . '(' . static::renderMethodParams($method) . ')';
        $method->hasReturnType() && $dcl .= ': ' . static::renderType($method->getReturnType());

        return $dcl;
    }

    protected static function renderMethodParams(ReflectionMethod $method): string
    {
        $dcl = $sep = '';
        foreach ($method->getParameters() as $param) {
            $dcl .= $sep . ($type = static::renderParamType($param));
            $sep = ', ';
            !empty($type) && $dcl .= ' ';
            $dcl .= static::renderParam($param);
        }

        return $dcl;
    }

    protected static function renderParam(ReflectionParameter $param): string
    {
        $dcl = '';
        $param->isPassedByReference() && $dcl .= '&';
        $dcl .= '$' . $param->getName();
        if ($param->isDefaultValueAvailable()) {
            $dcl .= ' = ';
            if ($param->isDefaultValueConstant()) {
                $dcl .= '\\' . $param->getDefaultValueConstantName();
            } else {
                $dcl .= var_export($param->getDefaultValue(), true);
            }
        }

        return $dcl;
    }

    protected static function renderParamType(ReflectionParameter $param): string
    {
        if (!$param->hasType()) {
            return '';
        }

        $dcl = '';
        $param->hasType() && $dcl .= static::renderType($param->getType());

        return $dcl;
    }

    protected static function renderType(ReflectionType $type): string
    {
        $dcl = '';
        ((string)$type)[0] === '?' && $dcl .= '?';
        if (instance_of($type, ReflectionNamedType::class)) {
            $dcl .= (!$type->isBuiltin() ? '\\' : '') . $type->getName();
        } elseif (instance_of($type, ReflectionUnionType::class)) {
            $sep = '';
            foreach ($type->getTypes() as $type) {
                $dcl .= $sep . static::renderType($type);
                $sep = '|';
            }
        }

        return $dcl;
    }
}
