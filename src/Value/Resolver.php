<?php

namespace Whitecube\NovaFlexibleContent\Value;

use Illuminate\Support\Collection;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class Resolver implements ResolverInterface
{

    /**
     * @inerhitDoc
     */
    public function set($model, $attribute, $groups)
    {
        return $model->$attribute = $groups->map(function (Layout $group) {
            return [
                'layout'     => $group->name(),
                'key'        => $group->key(),
                'collapsed'  => $group->isCollapsed(),
                'attributes' => $group->getAttributes(),
            ];
        });
    }

    /**
     * @inerhitDoc
     */
    public function get($model, $attribute, $groups)
    {
        return collect(
            $this->extractValueFromResource($model, $attribute)
        )->map(function ($item) use ($groups) {
            if ($layout = $groups->find($item->layout)) {
                return $layout->duplicateAndHydrate($item->key, (array) $item->attributes)
                              ->setCollapsed($item->collapsed ?? false);
            }

            return null;
        })->filter()->values();
    }

    /**
     * Find the attribute's value in the given resource
     *
     * @param mixed  $resource
     * @param string $attribute
     * @return array
     */
    protected function extractValueFromResource($resource, $attribute)
    {
        $value = data_get($resource, str_replace('->', '.', $attribute)) ?? [];

        if ($value instanceof Collection) {
            $value = $value->toArray();
        } elseif (is_string($value)) {
            $value = json_decode($value) ?? [];
        }

        // Fail silently in case data is invalid
        if (!is_array($value)) {
            return [];
        }

        return array_map(function ($item) {
            return is_array($item) ? (object) $item : $item;
        }, $value);
    }
}
