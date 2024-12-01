<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console;

use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\Compiler\Asset as AssetCompiler;
use Lightna\Engine\App\Compiler\ClassMap as ClassMapCompiler;
use Lightna\Engine\App\Compiler\Config as ConfigCompiler;
use Lightna\Engine\App\Compiler\Layout as LayoutCompiler;
use Lightna\Engine\App\Compiler\ObjectSchema as ObjectSchemaCompiler;
use Lightna\Engine\App\Compiler\Plugin as PluginCompiler;
use Lightna\Engine\App\Compiler\Preload as PreloadCompiler;
use Lightna\Engine\App\Compiler\Template as TemplateCompiler;
use Lightna\Engine\App\Config as AppConfig;
use Lightna\Engine\App\Opcache\Compiled;

class Compile extends CommandA
{
    protected array $config;
    protected Compiled $compiled;

    public function run(): void
    {
        $this->init();

        $this->printStart('clean compiled');
        $this->compiled->clean();
        $this->printEnd();

        $this->runItem([
            'message' => 'make objects map',
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

        $assetDir = getobj(AppConfig::class)->get('asset_dir');
        rcleandir(LIGHTNA_ENTRY . $assetDir);

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
                'message' => 'make assets',
                'compiler' => getobj(AssetCompiler::class),
            ],
        ]);

        $this->runCompilersInModules();
        $this->version();
    }

    protected function init(array $data = []): void
    {
        $config = require LIGHTNA_ENTRY . 'config.php';
        foreach (['App/Bootstrap.php', 'App/Compiler/CompilerA.php', 'App/Compiler/ClassMap.php'] as $file) {
            require LIGHTNA_ENTRY . $config['src_dir'] . '/' . $file;
        }
        Bootstrap::declaration($config);
        $this->compiled = new Compiled();
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

    protected function version(): void
    {
        $this->compiled->save('version', time());
    }
}
