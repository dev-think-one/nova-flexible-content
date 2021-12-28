<?php

namespace NovaFlexibleContent\Tests\Layouts;

use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Tests\TestCase;

class AttributesManipulationTest extends TestCase
{
    /** @test */
    public function implements_array_access()
    {
        $initialData = [
            'foo' => 'FOO value',
            'bar' => 'BAR value',
            'baz' => 'BAZ value',
        ];

        $layout = (new Layout())->setRawAttributes($initialData, true);

        foreach (array_keys($initialData) as $key) {
            $this->assertEquals($initialData[$key], $layout[$key]);
            $this->assertTrue(isset($layout[$key]));
            $this->assertFalse(empty($layout[$key]));
        }
        $this->assertNull($layout['fake-key']);
        $this->assertFalse(isset($layout['fake-key']));
        $this->assertTrue(empty($layout['fake-key']));

        $layout['bar'] = 'BAR new val';
        $this->assertEquals('BAR new val', $layout['bar']);
        $this->assertTrue(isset($layout['bar']));
        $this->assertFalse(empty($layout['bar']));

        $layout['fake-key'] = 'Fake new val';
        $this->assertEquals('Fake new val', $layout['fake-key']);
        $this->assertTrue(isset($layout['fake-key']));
        $this->assertFalse(empty($layout['fake-key']));

        unset($layout['bar']);
        unset($layout['fake-key']);
        $this->assertNull($layout['fake-key']);
        $this->assertNull($layout['bar']);
        $this->assertFalse(isset($layout['fake-key']));
        $this->assertFalse(isset($layout['bar']));
        $this->assertTrue(empty($layout['fake-key']));
        $this->assertTrue(empty($layout['bar']));
    }

    /** @test */
    public function implements_magic_access()
    {
        $initialData = [
            'foo' => 'FOO value',
            'bar' => 'BAR value',
            'baz' => 'BAZ value',
        ];

        $layout = (new Layout())->setRawAttributes($initialData, true);

        foreach (array_keys($initialData) as $key) {
            $this->assertEquals($initialData[$key], $layout->$key);
            $this->assertTrue(isset($layout->$key));
            $this->assertFalse(empty($layout->$key));
        }
        $key = 'fake_key';
        $this->assertNull($layout->$key);
        $this->assertFalse(isset($layout->$key));
        $this->assertTrue(empty($layout->$key));

        $layout->bar = 'BAR new val';
        $this->assertEquals('BAR new val', $layout->bar);
        $this->assertTrue(isset($layout->bar));
        $this->assertFalse(empty($layout->bar));

        $layout->fake_key = 'Fake new val';
        $this->assertEquals('Fake new val', $layout->fake_key);
        $this->assertTrue(isset($layout->fake_key));
        $this->assertFalse(empty($layout->fake_key));

        unset($layout->bar);
        unset($layout->fake_key);
        $this->assertNull($layout->fake_key);
        $this->assertNull($layout->bar);
        $this->assertFalse(isset($layout->fake_key));
        $this->assertFalse(isset($layout->bar));
        $this->assertTrue(empty($layout->fake_key));
        $this->assertTrue(empty($layout->bar));
    }
}
