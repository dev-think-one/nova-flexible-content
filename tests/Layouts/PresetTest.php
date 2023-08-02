<?php

namespace NovaFlexibleContent\Tests\Layouts;

use Mockery\MockInterface;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Layouts\Preset;
use NovaFlexibleContent\Tests\Fixtures\Layouts\SimpleTextLayout;
use NovaFlexibleContent\Tests\TestCase;

class PresetTest extends TestCase
{

    /** @test */
    public function statically_init_and_set_layouts()
    {
        $preset = Preset::withLayouts([
            'foo' => SimpleTextLayout::class,
            SimpleTextLayout::class,
            'baz' => new SimpleTextLayout(),
            SimpleTextLayout::class,
        ]);

        $layouts = $preset->layouts();

        $this->assertCount(3, $layouts);
        $this->assertEquals('foo|simple-text|baz', implode('|', array_keys($layouts)));
        foreach ($layouts as $layout) {
            $this->assertIsString($layout);
            $this->assertEquals(SimpleTextLayout::class, $layout);
        }
    }

    /** @test */
    public function handle_preset()
    {
        $preset = Preset::withLayouts([
            'foo' => Layout::class,
            SimpleTextLayout::class,
            'baz' => new SimpleTextLayout(),
        ]);

        $field = Flexible::make('FooBar');

        $this->assertEmpty($field->layouts());

        $preset->handle($field);

        $this->assertNotEmpty($field->layouts());
        $this->assertCount(3, $field->layouts());
    }

    /** @test */
    public function handle_preset_calling_useLayout()
    {
        $preset = Preset::withLayouts([
            'foo' => Layout::class,
            SimpleTextLayout::class,
            'baz' => new SimpleTextLayout(),
        ]);

        $mock = $this->mock(Flexible::class, function (MockInterface $mock) {
            $mock->shouldReceive('useLayout')->with(Layout::class)->andReturnSelf();
            $mock->shouldReceive('useLayout')->with(SimpleTextLayout::class)->andReturnSelf();
            $mock->shouldReceive('useLayout')->with(SimpleTextLayout::class)->andReturnSelf();
        });

        $this->assertInstanceOf(Preset::class, $preset->handle($mock));
    }
}
