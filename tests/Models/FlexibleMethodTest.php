<?php

namespace NovaFlexibleContent\Tests\Models;

use NovaFlexibleContent\Layouts\Collections\LayoutsCollection;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Tests\Fixtures\Models\Post;
use NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\Feature\FeatureListLayout;
use NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\Feature\LinkLayout;
use NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\SimpleNumberLayout;
use NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\TeamMemberLayout;
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
        $this->assertInstanceOf(LayoutsCollection::class, $layout->flexibleLinks);
        $this->assertCount(2, $layout->flexibleLinks);
        $this->assertInstanceOf(LinkLayout::class, $layout->flexibleLinks[0]);

        // Get using function.
        $this->assertCount(2, $layout->groups('links'));
        $this->assertCount(2, $layout->groups('links', 'link'));
        $this->assertCount(2, $layout->groups('links', ['link']));
        $this->assertCount(0, $layout->groups('links', ['image']));
        $this->assertInstanceOf(LinkLayout::class, $layout->group('links'));
        $this->assertInstanceOf(LinkLayout::class, $layout->group('links', 'link'));
        $this->assertInstanceOf(LinkLayout::class, $layout->group('links', ['link']));
        $this->assertNull($layout->group('links', ['image']));

        // If preset not exists
        $this->assertInstanceOf(LayoutsCollection::class, $layout->groups('images'));
        $this->assertCount(0, $layout->groups('images'));
        $this->assertNull($layout->group('images'));

        // Get from attribute.
        $this->assertEquals('default.svg', $layout->imageLink);
        $this->assertEquals('default.svg', $layout->image_link);
    }

    /** @test */
    public function to_flexible_collection_as_collection()
    {
        /** @var Post $post */
        $post = Post::factory()->create([]);

        $value = collect([
            [
                'key'        => 'yRhzFoRs6X5CYyz9',
                'layout'     => 'team_member',
                'attributes' => [
                    'member' => '102',
                ],
            ],
            [
                'key'        => 'smDwwm3EpPMT7awi',
                'layout'     => 'team_member',
                'attributes' => [
                    'member' => null,
                ],
            ],
        ]);

        $layouts = $post->toFlexibleCollection($value, [
            'team_member' => TeamMemberLayout::class,
        ]);

        $this->assertCount(2, $layouts);
        $this->assertInstanceOf(TeamMemberLayout::class, $layouts->first());
    }

    /** @test */
    public function to_flexible_collection_from_array_of_different_types()
    {
        /** @var Post $post */
        $post = Post::factory()->create([]);

        $value = collect([
            // Supports raw string
            '{"layout":"team_member","key":"sUO4zKOZ0Efp6mxj","attributes":{"member":"102"}}',
            // Empty name skipped
            '{"layout":"","key":"sUO4zKOZ0E2p6mxj","attributes":{"member":"102"}}',
            // Supports "\stdClass"
            json_decode('{"layout":"team_member","key":"smDwwm3EpPMT7awi","attributes":{"member":""}}'),
            // Supports layout
            SimpleNumberLayout::make(),
            TeamMemberLayout::make(),
        ]);

        $layouts = $post->toFlexibleCollection($value, [
            'team_member' => TeamMemberLayout::class,
        ]);

        $this->assertCount(4, $layouts);
        $this->assertInstanceOf(TeamMemberLayout::class, $layouts->get(0));
        $this->assertInstanceOf(TeamMemberLayout::class, $layouts->get(1));
        // Moved to simple layout because we have no
        $this->assertInstanceOf(Layout::class, $layouts->get(2));
        $this->assertInstanceOf(TeamMemberLayout::class, $layouts->get(3));
    }
}
