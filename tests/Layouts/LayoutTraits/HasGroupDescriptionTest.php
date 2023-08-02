<?php

namespace NovaFlexibleContent\Tests\Layouts\LayoutTraits;

use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Tests\TestCase;

class HasGroupDescriptionTest extends TestCase
{
    /** @test */
    public function has_mutator()
    {
        $layout = new Layout();

        $this->assertNull($layout->fieldUsedForDescription());
        $this->assertNull($layout->jsonSerialize()['configs']['fieldUsedForDescription']);

        $layout->useFieldForDescription('fooBarBaz');

        $this->assertEquals('fooBarBaz', $layout->fieldUsedForDescription());
        $this->assertEquals('fooBarBaz', $layout->jsonSerialize()['configs']['fieldUsedForDescription']);
    }
}
