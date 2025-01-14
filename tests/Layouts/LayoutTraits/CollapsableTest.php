<?php

namespace NovaFlexibleContent\Tests\Layouts\LayoutTraits;

use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Tests\TestCase;

class CollapsableTest extends TestCase
{
    /** @test */
    public function manipulate_states()
    {
        $layout = new Layout();

        $this->assertFalse($layout->isCollapsed());
        $this->assertInstanceOf(Layout::class, $layout->setCollapsed());
        $this->assertTrue($layout->isCollapsed());
        $this->assertInstanceOf(Layout::class, $layout->setCollapsed(false));
        $this->assertFalse($layout->isCollapsed());
    }
}
