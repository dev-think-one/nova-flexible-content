<?php

namespace NovaFlexibleContent\Tests\Layouts;

use Illuminate\Support\Str;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Tests\TestCase;

class HasLayoutKeyTest extends TestCase
{
    /** @test */
    public function keys_generated_by_default_if_not_empty()
    {
        $layout = new Layout();

        $this->assertNull($layout->key());
        $this->assertNull($layout->inUseKey());
        $this->assertFalse($layout->isUseKey('foo'));

        $randomInvalidKey = Str::random(10);
        $layout           = new Layout(key: $randomInvalidKey);

        $this->assertNotNull($layout->key());
        $this->assertNotEquals($randomInvalidKey, $layout->key());
        $this->assertNotNull($layout->inUseKey());
        $this->assertEquals($randomInvalidKey, $layout->inUseKey());
        $this->assertTrue($layout->isUseKey($randomInvalidKey));
        $this->assertTrue($layout->isUseKey($layout->key()));

        $validKey = '266e6d32f7b1b12c';
        $layout   = new Layout(key: $validKey);
        $this->assertNotNull($layout->key());
        $this->assertEquals($validKey, $layout->key());
    }
}
