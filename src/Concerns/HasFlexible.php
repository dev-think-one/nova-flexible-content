<?php

namespace NovaFlexibleContent\Concerns;

use Illuminate\Support\Collection as BaseCollection;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Layouts\LayoutsCollection;

trait HasFlexible
{

    /**
     * Parse a Flexible Content attribute.
     */
    public function flexible(string $attribute, array $layoutMapping = []): LayoutsCollection
    {
        $value = data_get($this->attributes, $attribute);

        return $this->toFlexibleCollection($value ?: null, $layoutMapping);
    }

    /**
     * Parse a Flexible Content from value.
     */
    public function toFlexibleCollection(mixed $value, array $layoutMapping = []): LayoutsCollection
    {
        $flexible = $this->getFlexibleArrayFromValue($value);

        if (is_null($flexible)) {
            return new LayoutsCollection();
        }

        return LayoutsCollection::make($this->getMappedFlexibleLayouts($flexible, $layoutMapping))->filter();
    }

    /**
     * Transform incoming value into an array of usable layouts.
     */
    protected function getFlexibleArrayFromValue(mixed $value): ?array
    {
        if (is_string($value)) {
            $value = json_decode($value);

            return is_array($value) ? $value : null;
        }

        if (is_a($value, BaseCollection::class)) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Map array with Flexible Content Layouts.
     */
    protected function getMappedFlexibleLayouts(array $flexible, array $layoutMapping): array
    {
        return array_map(function ($item) use ($layoutMapping) {
            return $this->getMappedLayout($item, $layoutMapping);
        }, $flexible);
    }

    /**
     * Transform given layout value into a usable Layout instance.
     */
    protected function getMappedLayout(mixed $item, array $layoutMapping): ?Layout
    {
        $name       = null;
        $key        = null;
        $attributes = [];

        if (is_string($item)) {
            $item = json_decode($item, true);
        }

        if (is_a($item, \stdClass::class)) {
            $item = json_decode(json_encode($item), true);
        }

        if (is_array($item)) {
            $name       = $item['layout']             ?? null;
            $key        = $item['key']                ?? null;
            $attributes = (array) $item['attributes'] ?? [];
        } elseif (is_a($item, Layout::class)) {
            $name       = $item->name();
            $key        = $item->key();
            $attributes = $item->getAttributes();
        }

        if (is_null($name)) {
            return null;
        }

        return $this->createMappedLayout($name, $key, $attributes, $layoutMapping);
    }

    /**
     * Transform given layout value into a usable Layout instance.
     */
    protected function createMappedLayout(string $name, string $key, array $attributes, array $layoutMapping): Layout
    {
        $classname = array_key_exists($name, $layoutMapping)
            ? $layoutMapping[$name]
            : Layout::class;

        $layout = new $classname($name, $name, [], $key, $attributes);

        $layout->setModel($this);

        return $layout;
    }
}
