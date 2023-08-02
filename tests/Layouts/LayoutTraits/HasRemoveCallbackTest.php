<?php

namespace NovaFlexibleContent\Tests\Layouts\LayoutTraits;

use Laravel\Nova\Http\Requests\NovaRequest;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Nova\Fields\ImageForFlexible;
use NovaFlexibleContent\Tests\Fixtures\Layouts\TeamMemberLayout;
use NovaFlexibleContent\Tests\Fixtures\Models\Post;
use NovaFlexibleContent\Tests\TestCase;

class HasRemoveCallbackTest extends TestCase
{

    /** @test */
    public function recursive_fire_remove_callback()
    {
        $value = '[{"layout":"team_member","key":"sUO4zKOZ0Efp6mxj","attributes":{"member":"102"}},{"layout":"team_member","key":"smDwwm3EpPMT7awi","attributes":{"member":""}},{"layout":"team_member","key":"s9qALgu1QiC0sNY0","attributes":{"member":"168"}},{"layout":"team_member","key":"sS6LbfedCBb8bkyG","attributes":{"member":"169"}},{"layout":"team_member","key":"sYX5nek7oKXtAuZI","attributes":{"member":"170"}}]';

        $post          = new Post();
        $post->content = $value;

        $flexible = Flexible::make('Foo')
            ->useLayout(TeamMemberLayout::class);

        $this->assertEmpty($flexible->groups());

        $flexible->resolve($post, 'content');

        $this->assertNotEmpty($flexible->groups());
        $this->assertCount(5, $flexible->groups());

        /** @var Layout $group */
        foreach ($flexible->groups() as $group) {
            foreach ($group->fieldsCollection() as $field) {
                if ($field instanceof ImageForFlexible) {
                    $field->delete(function ($request, $model, $storageDisk, $storagePath) use ($field) {
                        $this->assertInstanceOf(Post::class, $model);
                        $this->assertEquals($field->value, $storagePath);
                    });
                }
            }
        }

        $flexible->groups()->fireRemoveCallback($flexible, app(NovaRequest::class), $post);
    }

}
