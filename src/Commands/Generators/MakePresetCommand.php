<?php

namespace NovaFlexibleContent\Commands\Generators;

class MakePresetCommand extends GeneratorCommand
{
    protected $signature = 'flexible:make:preset
        {classname? : The preset\'s classname}
    ';

    protected $description = 'Generate a new Flexible Content Field configuration Preset';

    /**
     * The preset's classname.
     */
    protected string $classname;

    protected function parseArguments(): void
    {
        $this->classname = $this->argument('classname');
    }

    protected function stubFilePath(): string
    {
        return StubPublishCommand::stubPath('preset.stub');
    }

    protected function generatedFilePath(): string
    {
        return sprintf(config('nova-flexible-content.stubs.to.preset'), $this->classname);
    }

    protected function buildReplaces(): array
    {
        return [
            '___CLASSNAME___' => $this->classname,
        ];
    }
}
