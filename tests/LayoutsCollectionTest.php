<?php

namespace NovaFlexibleContent\Tests;

use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Layouts\LayoutsCollection;

class LayoutsCollectionTest extends TestCase
{
    /** @test */
    public function find_layout_by_name(): void
    {
        $collection = new LayoutsCollection([new Layout('Foo', 'bar')]);

        $this->assertNotNull($collection->find('bar'));
        $this->assertNull($collection->find('baz'));
    }
}
