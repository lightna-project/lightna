<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console;

use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\Build;
use Lightna\Engine\App\Compiler;
use Lightna\Engine\App\Compiler\Asset as AssetCompiler;
use Lightna\Engine\App\Compiler\ClassMap as ClassMapCompiler;
use Lightna\Engine\App\Compiler\Config as ConfigCompiler;
use Lightna\Engine\App\Compiler\Layout as LayoutCompiler;
use Lightna\Engine\App\Compiler\ObjectSchema as ObjectSchemaCompiler;
use Lightna\Engine\App\Compiler\Plugin as PluginCompiler;
use Lightna\Engine\App\Compiler\Preload as PreloadCompiler;
use Lightna\Engine\App\Compiler\Template as TemplateCompiler;
use Lightna\Engine\App\Compiler\Translate as TranslateCompiler;
use Lightna\Engine\App\Config as AppConfig;

class Compile extends CommandA
{
    protected Compiler $compiler;
    protected Build $build;

    public function run(): void
    {
        $this->init();

        if (Bootstrap::getCompilerMode() === 'default') {
            $this->printStart('clean build');
            $this->compiler->clean();
            $this->printEnd();
        }

        $this->runItem([
            'message' => 'make class map',
            'compiler' => new ClassMapCompiler(),
        ]);

        Bootstrap::autoload();

        $this->runSequence([
            [
                'message' => 'make config',
                'compiler' => new ConfigCompiler(),
            ],
            [
                'message' => 'make objects schema',
                'compiler' => new ObjectSchemaCompiler(),
            ],
            [
                'message' => 'make plugins',
                'compiler' => new PluginCompiler(),
            ],
        ]);

        // Re-init autoload
        Bootstrap::autoload();
        // Load object manager
        Bootstrap::objectManager();

        $this->runSequence([
            [
                'message' => 'make preload',
                'compiler' => getobj(PreloadCompiler::class),
            ],
            [
                'message' => 'make templates',
                'compiler' => getobj(TemplateCompiler::class),
            ],
            [
                'message' => 'make layout',
                'compiler' => getobj(LayoutCompiler::class),
            ],
            [
                'message' => 'make translates',
                'compiler' => getobj(TranslateCompiler::class),
            ],
            [
                'message' => 'make assets',
                'compiler' => getobj(AssetCompiler::class),
            ],
        ]);

        $this->runCompilersInModules();
        $this->compiler->version();
    }

    protected function init(array $data = []): void
    {
        Bootstrap::declaration();

        $this->compiler = new Compiler();
        $this->compiler->defineConfig();
        $this->compiler->init();
        $this->build = new Build();
        $this->build->init();
        parent::init($data);
    }

    protected function runSequence(array $sequence): void
    {
        foreach ($sequence as $item) {
            $this->runItem($item);
        }
    }

    protected function runItem(array $item): void
    {
        $this->printStart($item['message']);
        $compiler = is_object($item['compiler']) ? $item['compiler'] : getobj($item['compiler']);
        $compiler->make();
        $this->printEnd();
    }

    protected function runCompilersInModules(): void
    {
        if (!$pool = getobj(AppConfig::class)->get('compiler/pool')) {
            return;
        }

        $this->runSequence($pool);
    }

    public function apply(): void
    {
        $this->init();

        if (!is_dir($this->build->getDir())) {
            echo cli_warning('No build to apply') . "\n";
            return;
        }

        Bootstrap::autoload();
        Bootstrap::objectManager();

        $this->compiler->apply();
    }

    public function validate(): void
    {
        // Error wil be thrown in Bootstrap::validateBuild
        $this->init();
    }
}
