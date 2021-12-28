<?php

namespace NovaFlexibleContent\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

abstract class GeneratorCommand extends Command
{
    protected Filesystem $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(): int
    {
        $this->parseArguments();

        $path = $this->makeDirectory($this->generatedFilePath());

        $this->files->put($path, $this->buildClass());

        $this->info('Generated '.$path);

        return 0;
    }

    /**
     * Create the directories if they do not exist yet
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory(string $path): string
    {
        $directory = dirname($path);

        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true, true);
        }

        return $path;
    }

    /**
     * Generate the file's content
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass(): string
    {
        $replaces = $this->buildReplaces();

        return str_replace(
            array_keys($replaces),
            array_values($replaces),
            $this->files->get($this->stubFilePath())
        );
    }

    abstract protected function parseArguments(): void;

    /**
     * @return string - Absolute path to stub template file.
     */
    abstract protected function stubFilePath(): string;

    /**
     * @return string - Absolute path to new file.
     */
    abstract protected function generatedFilePath(): string;

    /**
     * @return array Key: string to replace, Value: replaces value.
     */
    abstract protected function buildReplaces(): array;
}
