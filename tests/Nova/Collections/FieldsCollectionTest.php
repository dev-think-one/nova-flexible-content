<?php

namespace NovaFlexibleContent\Tests\Nova\Collections;

use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Nova\Collections\FieldsCollection;
use NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\Feature\FeatureListLayout;
use NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\SimpleNumberLayout;
use NovaFlexibleContent\Tests\TestCase;

class FieldsCollectionTest extends TestCase
{
    /** @test */
    public function find_field_by_attribute()
    {
        $collection = FieldsCollection::make([
            Text::make('Foo'),
            Flexible::make('Bar')
                ->useLayout(SimpleNumberLayout::make())
                ->useLayout(FeatureListLayout::make()),
            Number::make('Baz'),
        ]);

        $this->assertInstanceOf(Text::class, $collection->findFieldByAttribute('foo'));
        $this->assertInstanceOf(Flexible::class, $collection->findFieldByAttribute('bar'));
        $this->assertInstanceOf(Number::class, $collection->findFieldByAttribute('baz'));
    }
}
