<?php

namespace Tests\Unit\Layouts;

use PHPUnit\Framework\TestCase;
use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Whitecube\NovaFlexibleContent\Layouts\LayoutsCollection;

class CollectionTest extends TestCase
{
    public function testFind(): void
    {
        $collection = new LayoutsCollection([new Layout('Foo', 'bar')]);

        $this->assertNotNull($collection->find('bar'));
        $this->assertNull($collection->find('a-name'));
    }
}
