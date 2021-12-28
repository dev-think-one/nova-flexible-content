<?php

namespace NovaFlexibleContent\Tests\Commands;

use NovaFlexibleContent\Commands\Generators\StubPublishCommand;
use NovaFlexibleContent\Tests\TestCase;

class MakeLayoutTest extends TestCase
{
    /** @test */
    public function make_file_from_package_stub()
    {
        StubPublishTest::clearPublishedStubsFolder();

        $classname  = 'FooTestLayout';
        $layoutName = 'bar-baz';
        $this->artisan("flexible:make:layout {$classname} \"{$layoutName}\"")->assertSuccessful();

        $filePath = sprintf(config('nova-flexible-content.stubs.to.layout'), $classname);
        $this->assertFileExists($filePath);
        $this->assertStringContainsString("class {$classname}", file_get_contents($filePath));
        $this->assertStringContainsString("\$name = '{$layoutName}'", file_get_contents($filePath));

        unlink($filePath);
    }

    /** @test */
    public function make_file_from_custom_stub()
    {
        $this->artisan('flexible:stub:publish')->assertSuccessful();
        $newStubPath = StubPublishCommand::stubsPath();
        file_put_contents($newStubPath.'layout.stub', 'Foo ___CLASSNAME___ Bar ___NAME___ Baz');

        $classname = 'FooTestLayout';
        $this->artisan("flexible:make:layout {$classname}")->assertSuccessful();

        $filePath = sprintf(config('nova-flexible-content.stubs.to.layout'), $classname);
        $this->assertFileExists($filePath);
        $layoutName = strtolower($classname);
        $this->assertStringContainsString("Foo {$classname} Bar {$layoutName} Baz", file_get_contents($filePath));

        unlink($filePath);
        StubPublishTest::clearPublishedStubsFolder();
    }
}
