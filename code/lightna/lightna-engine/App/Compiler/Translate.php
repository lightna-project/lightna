<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Exception;

class Translate extends CompilerA
{
    public function make(): void
    {
        $this->saveTranslates($this->collectTranslates());
    }

    protected function collectTranslates(): array
    {
        $translates = [];
        $this->walkFilesInModules(
            'i18n',
            ['csv'],
            function ($subPath, $file) use (&$translates) {
                $translates = merge($translates, $this->loadTranslatesCsv($file));
            }
        );

        return $translates;
    }

    protected function loadTranslatesCsv(string $file): array
    {
        $locale = pathinfo($file, PATHINFO_FILENAME);
        $translates = [];
        $handle = fopen(LIGHTNA_ENTRY . $file, 'r');

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row) || $row === [null]) continue;
            if (count($row) !== 2) {
                throw new Exception(
                    'Invalid language file: ' . $file . '. Expected 2 values in the row, actual row: '
                    . var_export($row, true)
                );
            }

            $translates[$row[0]] = $row[1];
        }
        fclose($handle);

        return [$locale => $translates];
    }

    protected function saveTranslates(array $localeTranslates): void
    {
        foreach ($localeTranslates as $locale => $translates) {
            $this->build->save('translate/' . $locale, $translates);
        }
    }
}