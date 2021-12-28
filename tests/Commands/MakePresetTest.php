<?php

namespace NovaFlexibleContent\Tests\Commands;

use NovaFlexibleContent\Commands\Generators\StubPublishCommand;
use NovaFlexibleContent\Tests\TestCase;

class MakePresetTest extends TestCase
{
    /** @test */
    public function make_file_from_package_stub()
    {
        StubPublishTest::clearPublishedStubsFolder();

        $classname = 'FooTestPreset';
        $this->artisan("flexible:make:preset {$classname}")->assertSuccessful();

        $filePath = sprintf(config('nova-flexible-content.stubs.to.preset'), $classname);
        $this->assertFileExists($filePath);
        $this->assertStringContainsString("class {$classname}", file_get_contents($filePath));

        unlink($filePath);
    }

    /** @test */
    public function make_file_from_custom_stub()
    {
        $this->artisan('flexible:stub:publish')->assertSuccessful();
        $newStubPath = StubPublishCommand::stubsPath();
        file_put_contents($newStubPath.'preset.stub', 'Foo ___CLASSNAME___ Bar');

        $classname = 'FooTestPreset';
        $this->artisan("flexible:make:preset {$classname}")->assertSuccessful();

        $filePath = sprintf(config('nova-flexible-content.stubs.to.preset'), $classname);
        $this->assertFileExists($filePath);
        $this->assertStringContainsString("Foo {$classname} Bar", file_get_contents($filePath));

        unlink($filePath);
        StubPublishTest::clearPublishedStubsFolder();
    }
}
