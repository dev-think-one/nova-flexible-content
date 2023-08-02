<?php

namespace NovaFlexibleContent\Tests\Layouts\LayoutTraits;

use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Tests\TestCase;

class HasLimitPerLayoutTest extends TestCase
{
    /** @test */
    public function has_limit()
    {
        $layout = new Layout();

        $this->assertEquals(0, $layout->limit());
        $this->assertEquals(0, $layout->jsonSerialize()['limit']);

        $layout->useLimit(99);

        $this->assertEquals(99, $layout->limit());
        $this->assertEquals(99, $layout->jsonSerialize()['limit']);
    }
}
