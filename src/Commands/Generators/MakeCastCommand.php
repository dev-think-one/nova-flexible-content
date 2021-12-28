<?php

namespace NovaFlexibleContent\Commands\Generators;

class MakeCastCommand extends GeneratorCommand
{
    protected $signature = 'flexible:make:cast
        {classname : The cast\'s classname}
    ';

    protected $description = 'Generate a new Flexible Content cast class';

    protected string $classname = '';

    protected function parseArguments(): void
    {
        $this->classname = $this->argument('classname');
    }

    protected function stubFilePath(): string
    {
        return StubPublishCommand::stubPath('cast.stub');
    }

    protected function generatedFilePath(): string
    {
        return sprintf(config('nova-flexible-content.stubs.to.cast'), $this->classname);
    }

    protected function buildReplaces(): array
    {
        return [
            '___CLASSNAME___' => $this->classname,
        ];
    }
}
