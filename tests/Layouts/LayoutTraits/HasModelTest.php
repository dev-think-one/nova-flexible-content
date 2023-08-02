<?php

namespace NovaFlexibleContent\Tests\Layouts\LayoutTraits;

use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Tests\Fixtures\Models\Post;
use NovaFlexibleContent\Tests\TestCase;

/**
 * @deprecated
 */
class HasModelTest extends TestCase
{
    /** @test */
    public function has_mutator()
    {
        $layout = Layout::make();

        $this->assertNull($layout->model());

        $layout->setModel(new Post());

        $this->assertInstanceOf(Post::class, $layout->model());
    }
}
