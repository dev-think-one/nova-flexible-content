<?php

namespace NovaFlexibleContent\Tests\Layouts;

use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Http\ScopedRequest;
use NovaFlexibleContent\Tests\Fixtures\Models\Post;
use NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\SimpleNumberLayout;
use NovaFlexibleContent\Tests\TestCase;

class LayoutTest extends TestCase
{
    /** @test */
    public function fill()
    {
        $layout = SimpleNumberLayout::make();

        $this->assertNull($layout->getAttribute('order'));

        $layout->fillFromRequest(app(ScopedRequest::class)->merge([
            'order' => '33',
        ]));

        $this->assertEquals(33, $layout->getAttribute('order'));
    }

    /** @test */
    public function clone_field()
    {
        $value = '[{"layout":"simple_number","key":"sUO4zKOZ0Efp6mxj","attributes":{"order":"23"}}]';

        $post          = new Post();
        $post->content = $value;

        $flexible = Flexible::make('Foo')
            ->useLayout(SimpleNumberLayout::class);

        $flexible->resolveForDisplay($post, 'content');

        $this->assertCount(1, $flexible->groups());
    }
}
