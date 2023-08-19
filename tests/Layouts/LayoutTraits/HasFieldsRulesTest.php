<?php

namespace NovaFlexibleContent\Tests\Layouts\LayoutTraits;

use NovaFlexibleContent\Http\FlexibleAttribute;
use NovaFlexibleContent\Http\ScopedRequest;
use NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\SimpleNumberLayout;
use NovaFlexibleContent\Tests\TestCase;

class HasFieldsRulesTest extends TestCase
{
    /** @test */
    public function generate_rules()
    {
        $layout = SimpleNumberLayout::make();

        $rules = $layout->generateRules(app(ScopedRequest::class), 'content', 'creation');

        $this->assertInstanceOf(FlexibleAttribute::class, $rules['content.attributes.order']['attribute']);
        $this->assertIsArray($rules['content.attributes.order']['rules']);
        $this->assertCount(4, $rules['content.attributes.order']['rules']);

        $rules = $layout->generateRules(app(ScopedRequest::class), 'content', 'update');

        $this->assertInstanceOf(FlexibleAttribute::class, $rules['content.attributes.order']['attribute']);
        $this->assertIsArray($rules['content.attributes.order']['rules']);
        $this->assertCount(5, $rules['content.attributes.order']['rules']);
    }
}
