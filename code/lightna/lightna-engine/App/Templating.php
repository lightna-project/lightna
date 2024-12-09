<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class Templating extends ObjectA
{
    protected Build $build;
    protected array $templateMap;
    protected array $templateSchema;

    protected function init(array $data = []): void
    {
        $this->templateMap = $this->build->load('template/map');
        $this->templateSchema = $this->build->load('template/schema');
    }

    public function getTemplateSchema(string $templateName): array
    {
        return $this->templateSchema[$templateName];
    }

    public function render(string $templateName, array $data = []): void
    {
        $this->proceed(
            LIGHTNA_ENTRY . $this->templateMap[$templateName],
            $this->getTemplateVars($templateName, $data),
        );
    }

    protected function proceed(string $templateName, array $data = []): void
    {
        extract($data);
        require $templateName;
    }

    protected function getTemplateVars(string $templateName, array $data = []): array
    {
        foreach ($this->templateSchema[$templateName] as $name => $type) {
            if (!array_key_exists($name, $data)) {
                $data[$name] = getobj($type);
            }
        }

        return $data;
    }
}
