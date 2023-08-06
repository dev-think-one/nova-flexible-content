<?php

namespace NovaFlexibleContent\Value;

use Illuminate\Support\Collection;
use NovaFlexibleContent\Layouts\Collections\GroupsCollection;
use NovaFlexibleContent\Layouts\Collections\LayoutsCollection;
use NovaFlexibleContent\Layouts\Layout;

class DefaultResolver implements Resolver
{

    /**
     * @inerhitDoc
     */
    public function set(mixed $resource, string $attribute, GroupsCollection $groups): string
    {
        return $resource->$attribute = $groups->map(function (Layout $group) {
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
    public function get(mixed $resource, string $attribute, LayoutsCollection $groups): GroupsCollection
    {
        return GroupsCollection::make(
            $this->extractValueFromResource($resource, $attribute)
        )->map(function ($item) use ($groups, $attribute) {
            if($item instanceof Layout) {
                return $item;
            }

            if ($layout = $groups->find($item->layout)) {
                return $layout->duplicate($item->key, (array)$item->attributes)
                    ->setCollapsed((bool)($item->collapsed ?? false));
            }

            return null;
        })->filter()->values();
    }

    /**
     * Find the attribute's value in the given resource
     *
     * @param mixed $resource
     * @param string $attribute
     * @return array
     */
    protected function extractValueFromResource($resource, $attribute)
    {
        $value = data_get($resource, str_replace('->', '.', $attribute)) ?? [];

        if ($value instanceof Collection) {
            $value = $value->all();
        } elseif (is_string($value)) {
            $value = json_decode($value) ?? [];
        }

        // Fail silently in case data is invalid
        if (!is_array($value)) {
            return [];
        }

        return array_map(function ($item) {
            return is_array($item) ? (object)$item : $item;
        }, $value);
    }
}
