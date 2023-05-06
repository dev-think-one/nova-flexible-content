<?php

namespace NovaFlexibleContent\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class StubPublishCommand extends Command
{
    protected $signature = 'flexible:stub:publish
        {--force : Overwrite any existing files}
    ';

    protected $description = 'Publish all stubs that are available for customization';

    public function handle(): int
    {
        if (!is_dir($stubsPath = static::stubsPath())) {
            (new Filesystem)->makeDirectory($stubsPath, 0755, true);
        }

        $files = [
            static::stubPath('layout.stub', true)   => $stubsPath.'/layout.stub',
            static::stubPath('preset.stub', true)   => $stubsPath.'/preset.stub',
            static::stubPath('resolver.stub', true) => $stubsPath.'/resolver.stub',
        ];

        foreach ($files as $from => $to) {
            if (!file_exists($to) || $this->option('force')) {
                file_put_contents($to, file_get_contents($from));
            }
        }

        $this->info('Stubs published successfully.');

        return 0;
    }

    public static function stubsPath(): string
    {
        return rtrim(config('nova-flexible-content.stubs.path', 'stubs'), '/').'/';
    }

    public static function stubPath(string $stubName, bool $strictFromPackage = false): string
    {
        $stubName = ltrim($stubName, '/');
        if (!$strictFromPackage) {
            $overrideStub = static::stubsPath().$stubName;
            if (file_exists($overrideStub)) {
                return $overrideStub;
            }
        }

        return __DIR__.'/../../../resources/stubs/'.$stubName;
    }
}
