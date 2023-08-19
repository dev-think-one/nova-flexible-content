<?php

namespace NovaFlexibleContent\Tests\Layouts\LayoutTraits;

use NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\SimpleNumberLayout;
use NovaFlexibleContent\Tests\TestCase;

class ModelEmulatesTest extends TestCase
{
    /** @test */
    public function force_fill()
    {
        $layout = SimpleNumberLayout::make();

        $this->assertNull($layout->getAttribute('order'));

        $layout->forceFill(['order' => 33]);

        $this->assertEquals(33, $layout->getAttribute('order'));
    }
}
