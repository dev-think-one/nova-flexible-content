<?php

namespace NovaFlexibleContent\Layouts;

use Illuminate\Support\Collection as BaseCollection;
use Laravel\Nova\Makeable;
use NovaFlexibleContent\Layouts\Collections\LayoutsCollection;

class ContentToFlexibleCollectionTransformer
{
    use Makeable;

    public function transform(mixed $value, array|Preset $layoutMapping = []): LayoutsCollection
    {
        $flexible = $this->getFlexibleArrayFromValue($value);

        if (is_null($flexible)) {
            return LayoutsCollection::make();
        }

        return LayoutsCollection::make($this->getMappedFlexibleLayouts($flexible, $layoutMapping))->filter()->values();
    }

    /**
     * Transform incoming value into an array of usable layouts.
     */
    protected function getFlexibleArrayFromValue(mixed $value): ?array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);

            return is_array($value) ? $value : null;
        }

        if (is_a($value, BaseCollection::class)) {
            return $value->all();
        }

        if (is_array($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Map array with Flexible Content Layouts.
     */
    protected function getMappedFlexibleLayouts(array $flexible, array|Preset $layoutMapping = []): array
    {
        return array_map(function ($item) use ($layoutMapping) {
            return $this->getMappedLayout($item, $layoutMapping);
        }, $flexible);
    }

    /**
     * Transform given layout value into a usable Layout instance.
     */
    protected function getMappedLayout(mixed $item, array|Preset $layoutMapping = []): ?Layout
    {
        $name       = null;
        $key        = null;
        $attributes = [];

        if (is_a($item, \stdClass::class)) {
            $item = json_decode(json_encode($item), true);
        }

        if (is_string($item)) {
            $item = json_decode($item, true);
        }

        if (is_array($item)) {
            $name       = $item['layout']             ?? null;
            $key        = $item['key']                ?? null;
            $attributes = (array) $item['attributes'] ?? [];
        } elseif (is_a($item, Layout::class)) {
            $name       = $item->name();
            $key        = (string) $item->key();
            $attributes = $item->getAttributes();
        }

        if (!$name) {
            return null;
        }

        return $this->createMappedLayout($name, $key, $attributes, $layoutMapping);
    }

    /**
     * Transform given layout value into a usable Layout instance.
     */
    protected function createMappedLayout(string $name, string $key, array $attributes, array|Preset $layoutMapping = []): Layout
    {
        if($layoutMapping instanceof Preset) {
            $layoutMapping = $layoutMapping->layouts();
        }

        $classname = array_key_exists($name, $layoutMapping)
            ? $layoutMapping[$name]
            : Layout::class;

        return new $classname($name, $name, [], $key, $attributes);
    }
}
