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

class Compile extends CompileA
{
    public function run(): void
    {
        $this->init();

        $this->printStart('clean compiled');
        rcleandir(LIGHTNA_CODE);
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
    }
}
