<?php

namespace NovaFlexibleContent\Commands\Generators;

class MakeLayoutCommand extends GeneratorCommand
{
    protected $signature = 'flexible:make:layout
        {classname : The layout\'s classname}
        {name? : The layout\'s identifier}
    ';

    protected $description = 'Generate a new Flexible Content Field Layout';

    /**
     * The layout's classname.
     */
    protected string $classname = '';

    /**
     * The layout's name attribute.
     */
    protected string $layoutName = '';

    protected function parseArguments(): void
    {
        $this->classname       = $this->argument('classname');
        $this->layoutName      = $this->argument('name') ?: strtolower($this->classname);
    }

    protected function stubFilePath(): string
    {
        return StubPublishCommand::stubPath('layout.stub');
    }

    protected function generatedFilePath(): string
    {
        return sprintf(config('nova-flexible-content.stubs.to.layout'), $this->classname);
    }

    protected function buildReplaces(): array
    {
        return [
            '___CLASSNAME___' => $this->classname,
            '___NAME___'      => $this->layoutName,
        ];
    }
}
