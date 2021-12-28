<?php

namespace NovaFlexibleContent\Commands\Generators;

class MakeResolverCommand extends GeneratorCommand
{
    protected $signature = 'flexible:make:resolver
        {classname? : The resolver\'s classname}
    ';

    protected $description = 'Generate a new Flexible Content Field Resolver';

    /**
     * The resolver's classname
     */
    protected string $classname = '';

    protected function parseArguments(): void
    {
        $this->classname = $this->argument('classname');
    }

    protected function stubFilePath(): string
    {
        return StubPublishCommand::stubPath('resolver.stub');
    }

    protected function generatedFilePath(): string
    {
        return sprintf(config('nova-flexible-content.stubs.to.resolver'), $this->classname);
    }

    protected function buildReplaces(): array
    {
        return [
            '___CLASSNAME___' => $this->classname,
        ];
    }
}
