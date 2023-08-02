<?php

namespace NovaFlexibleContent\Tests\Layouts\Collections;

use NovaFlexibleContent\Layouts\Collections\LayoutsCollection;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Tests\TestCase;

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
