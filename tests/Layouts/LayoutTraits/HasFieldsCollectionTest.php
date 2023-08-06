<?php

namespace NovaFlexibleContent\Tests\Layouts\LayoutTraits;

use Laravel\Nova\Http\Requests\NovaRequest;
use NovaFlexibleContent\Tests\Fixtures\Layouts\Feature\LinkLayout;
use NovaFlexibleContent\Tests\Fixtures\Models\Post;
use NovaFlexibleContent\Tests\TestCase;

class HasFieldsCollectionTest extends TestCase
{
    /** @test */
    public function filter_for_details()
    {
        $layout = LinkLayout::make();

        $this->assertCount(4, $layout->fieldsCollection());

        $layout->filterForDetail(app(NovaRequest::class), new Post());

        $this->assertCount(3, $layout->fieldsCollection());
    }
}
