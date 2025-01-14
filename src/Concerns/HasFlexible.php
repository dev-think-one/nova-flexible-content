<?php

namespace NovaFlexibleContent\Concerns;

use Illuminate\Support\Collection as BaseCollection;
use NovaFlexibleContent\Layouts\Collections\LayoutsCollection;
use NovaFlexibleContent\Layouts\ContentToFlexibleCollectionTransformer;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Layouts\Preset;

trait HasFlexible
{

    /**
     * Parse a Flexible Content attribute.
     */
    public function flexible(string $attribute, array|Preset $layoutMapping = []): LayoutsCollection
    {
        $value = data_get($this->attributes, $attribute);

        return $this->toFlexibleCollection($value ?: null, $layoutMapping);
    }

    /**
     * Parse a Flexible Content from value.
     */
    public function toFlexibleCollection(mixed $value, array|Preset $layoutMapping = []): LayoutsCollection
    {
        return ContentToFlexibleCollectionTransformer::make()->transform($value, $layoutMapping)->each(fn (Layout $l) => $l->setModel($this));
    }

    /**
     * Transform incoming value into an array of usable layouts.
     * @deprecated see ContentToFlexibleCollectionTransformer
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
     * @deprecated see ContentToFlexibleCollectionTransformer
     */
    protected function getMappedFlexibleLayouts(array $flexible, array|Preset $layoutMapping = []): array
    {
        return array_map(function ($item) use ($layoutMapping) {
            return $this->getMappedLayout($item, $layoutMapping);
        }, $flexible);
    }

    /**
     * Transform given layout value into a usable Layout instance.
     * @deprecated see ContentToFlexibleCollectionTransformer
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
     * @deprecated see ContentToFlexibleCollectionTransformer
     */
    protected function createMappedLayout(string $name, string $key, array $attributes, array|Preset $layoutMapping = []): Layout
    {
        if($layoutMapping instanceof Preset) {
            $layoutMapping = $layoutMapping->layouts();
        }

        $classname = array_key_exists($name, $layoutMapping)
            ? $layoutMapping[$name]
            : Layout::class;

        $layout = new $classname($name, $name, [], $key, $attributes);

        $layout->setModel($this);

        return $layout;
    }
}
