<?php

namespace NovaFlexibleContent\Tests\Models;

use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Layouts\LayoutsCollection;
use NovaFlexibleContent\Tests\Fixtures\Layouts\Feature\FeatureListLayout;
use NovaFlexibleContent\Tests\Fixtures\Layouts\Feature\LinkLayout;
use NovaFlexibleContent\Tests\Fixtures\Models\Post;
use NovaFlexibleContent\Tests\TestCase;

class FlexibleMethodTest extends TestCase
{
    /** @test */
    public function resolve_empty_value()
    {
        /** @var Post $post */
        $post = Post::factory()->create();

        $this->assertNull($post->content);
        $this->assertInstanceOf(LayoutsCollection::class, $post->flexible('content'));
        $this->assertCount(0, $post->flexible('content'));

        $this->assertNull($post->not_exists_foo);
        $this->assertInstanceOf(LayoutsCollection::class, $post->flexible('not_exists_foo'));
        $this->assertCount(0, $post->flexible('not_exists_foo'));
    }

    /** @test */
    public function resolve_filled_value()
    {
        /** @var Post $post */
        $post = Post::factory()->create([
            'content' => '[{"key":"yRhzFoRs6X5CYyz9","layout":"feature-list","collapsed":false,"attributes":{"title":"Foo list","src":null,"links":[{"key":"rrXFJC9c88W9dXok","layout":"link","collapsed":false,"attributes":{"text":"Example","link":"https://example.com"}},{"key":"rrXFJC9c88W9dXos","layout":"link","collapsed":false,"attributes":{"text":"Example2","link":"https://example2.com"}}]}}]',
        ]);

        $this->assertNotNull($post->content);
        $this->assertInstanceOf(LayoutsCollection::class, $post->flexible('content'));
        $this->assertCount(1, $post->flexible('content'));

        $layout = $post->flexible('content')->find('feature-list');
        $this->assertInstanceOf(Layout::class, $layout);
        $this->assertIsArray($layout->links);
        $this->assertCount(2, $layout->links);
        $this->assertIsArray($layout->links[0]);

        $layout = $post->flexible('content', [
            'feature-list' => FeatureListLayout::class,
        ])->find('feature-list');
        $this->assertInstanceOf(FeatureListLayout::class, $layout);
        $this->assertInstanceOf(LayoutsCollection::class, $layout->links);
        $this->assertCount(2, $layout->links);
        $this->assertInstanceOf(LinkLayout::class, $layout->links[0]);
    }
}
