<?php

namespace NovaFlexibleContent\Tests\Layouts\LayoutTraits;

use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Tests\Fixtures\Layouts\Feature\FeatureListLayout;
use NovaFlexibleContent\Tests\Fixtures\Layouts\Feature\LinkLayout;
use NovaFlexibleContent\Tests\Fixtures\Models\Post;
use NovaFlexibleContent\Tests\TestCase;

class HasFlexibleFieldInLayoutTest extends TestCase
{
    /** @test */
    public function find_flexible_group_recursive()
    {
        $value = '[{"layout":"feature-list","key":"sUO4zKOZ0Efp6mxj","attributes":{"title":"Listing", "src":"file.png", "links":[{"layout":"link","key":"sUO2zKOZ0Efp6mxj","attributes":{"text":"Click me", "link":"http://foo.bar"}}]}}]';

        $post          = new Post();
        $post->content = $value;

        $flexible = Flexible::make('Foo')
            ->useLayout(FeatureListLayout::class);

        $flexible->resolveForDisplay($post, 'content');

        $this->assertCount(1, $flexible->groups());

        $featureListLayout = $flexible->groups()->first();

        $this->assertNull($featureListLayout->findFlexibleGroupRecursive('sUO2zKOZ0Efp6mxD'));
        $this->assertInstanceOf($featureListLayout::class, $featureListLayout->findFlexibleGroupRecursive('sUO4zKOZ0Efp6mxj'));
        $this->assertInstanceOf(LinkLayout::class, $featureListLayout->findFlexibleGroupRecursive('sUO2zKOZ0Efp6mxj'));
    }

    /** @test */
    public function find_group_recursive_and_set_attribute()
    {
        $value = '[{"layout":"feature-list","key":"sUO4zKOZ0Efp6mxj","attributes":{"title":"Listing", "src":"file.png", "links":[{"layout":"link","key":"sUO2zKOZ0Efp6mxj","attributes":{"text":"Click me", "link":"http://foo.bar"}}]}}]';

        $post          = new Post();
        $post->content = $value;

        $flexible = Flexible::make('Foo')
            ->useLayout(FeatureListLayout::class);

        $flexible->resolveForDisplay($post, 'content');

        $this->assertCount(1, $flexible->groups());

        $featureListLayout = $flexible->groups()->first();

        $this->assertFalse($featureListLayout->findGroupRecursiveAndSetAttribute('sUO2zKOZ0Efp6mxD', 'text', 'new_click'));

        $this->assertTrue($featureListLayout->findGroupRecursiveAndSetAttribute('sUO4zKOZ0Efp6mxj', 'title', 'new title'));
        $this->assertFalse($featureListLayout->findGroupRecursiveAndSetAttribute('sUO4zKOZ0Efp6mxj', 'text', 'new_click'));

        $this->assertFalse($featureListLayout->findGroupRecursiveAndSetAttribute('sUO2zKOZ0Efp6mxj', 'title', 'new title'));
        $this->assertTrue($featureListLayout->findGroupRecursiveAndSetAttribute('sUO2zKOZ0Efp6mxj', 'text', 'new_click'));
    }
}
