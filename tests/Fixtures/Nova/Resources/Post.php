<?php

namespace NovaFlexibleContent\Tests\Fixtures\Nova\Resources;

use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Tests\Fixtures\Layouts\Feature\FeatureListLayout;
use NovaFlexibleContent\Tests\Fixtures\Layouts\SimpleNumberLayout;

/**
 * @extends Resource<\NovaFlexibleContent\Tests\Fixtures\Models\Post>
 */
class Post extends Resource
{
    public static $model = \NovaFlexibleContent\Tests\Fixtures\Models\Post::class;

    public static $title = 'reference';

    public function fields(NovaRequest $request)
    {
        return [
            Text::make('Title', 'title'),
            Flexible::make('Content')
                ->useLayout(SimpleNumberLayout::make())
                ->useLayout(FeatureListLayout::make()),
        ];
    }
}
