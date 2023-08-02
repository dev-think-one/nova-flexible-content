<?php

namespace NovaFlexibleContent\Tests\Layouts\LayoutTraits;

use NovaFlexibleContent\Tests\Fixtures\Layouts\SimpleNumberLayout;
use NovaFlexibleContent\Tests\Fixtures\Layouts\SimpleTextLayout;
use NovaFlexibleContent\Tests\TestCase;

class HasNameAndTitleTest extends TestCase
{
    /** @test */
    public function developer_can_set_name()
    {
        $layout = SimpleTextLayout::make();

        $this->assertEquals('simple-text', $layout->name());
    }

    /** @test */
    public function has_default_name()
    {
        $layout = SimpleNumberLayout::make();

        $this->assertEquals('simple_number', $layout->name());
    }

    /** @test */
    public function developer_can_set_title()
    {
        $layout = SimpleTextLayout::make();

        $this->assertEquals('Simple Text Layout', $layout->title());
    }

    /** @test */
    public function has_default_title()
    {
        $layout = SimpleNumberLayout::make();

        $this->assertEquals('Simple Number', $layout->title());
    }
}
