<?php

declare(strict_types=1);

namespace Lightna\PhpUnit\App\Compiler;

use DOMDocument;
use Lightna\Engine\App\Compiler\CompilerA;
use SimpleXMLElement;

class PhpUnit extends CompilerA
{
    protected string $baseDir;

    public function make(): void
    {
        $this->saveUnitXmlConfig($this->createUnitXmlConfig());
        $this->saveIntegrationXmlConfig($this->createIntegrationXmlConfig());
        $this->saveUnitBootstrap($this->getUnitBootstrap());
        $this->saveIntegrationBootstrap($this->getIntegrationBootstrap());
    }

    protected function createUnitXmlConfig(): SimpleXMLElement
    {
        $xml = $this->getUnitXmlConfigTemplate();
        $testsuite = $xml->testsuites->testsuite;
        foreach ($this->getTestDirectories()['unit'] as $directory) {
            $testsuite->addChild('directory', $directory);
        }

        return $xml;
    }

    protected function createIntegrationXmlConfig(): SimpleXMLElement
    {
        $xml = $this->getIntegrationXmlConfigTemplate();
        $testsuite = $xml->testsuites->testsuite;
        foreach ($this->getTestDirectories()['integration'] as $directory) {
            $testsuite->addChild('directory', $directory);
        }

        return $xml;
    }

    protected function getUnitXmlConfigTemplate(): SimpleXMLElement
    {
        return simplexml_load_file($this->baseDir . 'phpunit.unit.template.xml');
    }

    protected function getIntegrationXmlConfigTemplate(): SimpleXMLElement
    {
        return simplexml_load_file($this->baseDir . 'phpunit.integration.template.xml');
    }

    protected function getTestDirectories(): array
    {
        $search = ['Test', 'App/Test'];
        $directories = ['unit' => [], 'integration' => []];
        foreach ($this->getEnabledModules() as $module) {
            $dir = $module['path'];
            if (preg_match('~(^|/)vendor/~', $dir)) {
                // Skip testing vendor modules
                continue;
            }
            foreach ($search as $folder) {
                if ($directory = realpath(LIGHTNA_ENTRY . $dir . "/$folder/Unit")) {
                    $directories['unit'][] = $directory;
                }
                if ($directory = realpath(LIGHTNA_ENTRY . $dir . "/$folder/Integration")) {
                    $directories['integration'][] = $directory;
                }
            }
        }

        return $directories;
    }

    protected function saveUnitXmlConfig(SimpleXMLElement $xml): void
    {
        $this->build->putFile('phpunit/config.unit.xml', $this->getXmlPretty($xml));
    }

    protected function saveIntegrationXmlConfig(SimpleXMLElement $xml): void
    {
        $this->build->putFile('phpunit/config.integration.xml', $this->getXmlPretty($xml));
    }

    protected function getXmlPretty(SimpleXMLElement $xml): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    protected function getUnitBootstrap(): string
    {
        $file = realpath($this->baseDir . 'bootstrap.unit.php');

        return "<?php\n\nrequire " . var_export($file, true) . ";\n";
    }

    protected function getIntegrationBootstrap(): string
    {
        $file = realpath($this->baseDir . 'bootstrap.integration.php');

        return "<?php\n\nrequire " . var_export($file, true) . ";\n";
    }

    protected function saveUnitBootstrap(string $bootstrap): void
    {
        $this->build->putFile('phpunit/bootstrap.unit.php', $bootstrap);
    }

    protected function saveIntegrationBootstrap(string $bootstrap): void
    {
        $this->build->putFile('phpunit/bootstrap.integration.php', $bootstrap);
    }

    /** @noinspection PhpUnused */
    protected function defineBaseDir(): void
    {
        $this->baseDir = __DIR__ . '/../../';
    }
}
