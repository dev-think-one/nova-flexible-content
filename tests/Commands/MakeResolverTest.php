<?php

namespace NovaFlexibleContent\Tests\Commands;

use NovaFlexibleContent\Commands\Generators\StubPublishCommand;
use NovaFlexibleContent\Tests\TestCase;

class MakeResolverTest extends TestCase
{
    /** @test */
    public function make_file_from_package_stub()
    {
        StubPublishTest::clearPublishedStubsFolder();

        $classname = 'FooTestResolver';
        $this->artisan("flexible:make:resolver {$classname}")->assertSuccessful();

        $filePath = sprintf(config('nova-flexible-content.stubs.to.resolver'), $classname);
        $this->assertFileExists($filePath);
        $this->assertStringContainsString("class {$classname}", file_get_contents($filePath));

        unlink($filePath);
    }

    /** @test */
    public function make_file_from_custom_stub()
    {
        $this->artisan('flexible:stub:publish')->assertSuccessful();
        $newStubPath = StubPublishCommand::stubsPath();
        file_put_contents($newStubPath.'resolver.stub', 'Foo ___CLASSNAME___ Bar');

        $classname = 'FooTestResolver';
        $this->artisan("flexible:make:resolver {$classname}")->assertSuccessful();

        $filePath = sprintf(config('nova-flexible-content.stubs.to.resolver'), $classname);
        $this->assertFileExists($filePath);
        $this->assertStringContainsString("Foo {$classname} Bar", file_get_contents($filePath));

        unlink($filePath);
        StubPublishTest::clearPublishedStubsFolder();
    }
}
