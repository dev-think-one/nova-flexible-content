<?php

namespace NovaFlexibleContent\Tests\Commands;

use FilesystemIterator;
use Illuminate\Filesystem\Filesystem;
use NovaFlexibleContent\Commands\Generators\StubPublishCommand;
use NovaFlexibleContent\Tests\TestCase;

class StubPublishTest extends TestCase
{
    public static function clearPublishedStubsFolder()
    {
        $newStubPath = StubPublishCommand::stubsPath();
        clearstatcache();
        if (file_exists($newStubPath)) {
            (new Filesystem)->deleteDirectory($newStubPath);
        }
    }

    /** @test */
    public function successful_publish_stubs()
    {
        static::clearPublishedStubsFolder();

        $newStubPath = StubPublishCommand::stubsPath();

        $this->artisan('flexible:stub:publish')
             ->assertSuccessful();

        $this->assertDirectoryExists($newStubPath);
        $this->assertFileExists($newStubPath.'layout.stub');

        $fi = new FilesystemIterator($newStubPath, FilesystemIterator::SKIP_DOTS);
        $this->assertEquals(3, iterator_count($fi));

        static::clearPublishedStubsFolder();
    }

    /** @test */
    public function force_override_stubs()
    {
        static::clearPublishedStubsFolder();

        $newStubPath = StubPublishCommand::stubsPath();

        $this->artisan('flexible:stub:publish')->assertSuccessful();
        file_put_contents($newStubPath.'layout.stub', 'Foo');
        $this->assertEquals('Foo', file_get_contents($newStubPath.'layout.stub'));

        $this->artisan('flexible:stub:publish --force')->assertSuccessful();
        $this->assertNotEquals('Foo', file_get_contents($newStubPath.'layout.stub'));
        $this->assertStringContainsString('___CLASSNAME___', file_get_contents($newStubPath.'layout.stub'));

        static::clearPublishedStubsFolder();
    }
}
