<?php

namespace NovaFlexibleContent\Value;

use Illuminate\Support\Collection;
use Laravel\Nova\Makeable;
use NovaFlexibleContent\Layouts\Collections\GroupsCollection;
use NovaFlexibleContent\Layouts\Collections\LayoutsCollection;
use NovaFlexibleContent\Layouts\Layout;

class DefaultResolver implements Resolver
{
    use Makeable;

    protected ?\Closure $set = null;
    protected ?\Closure $get = null;

    public function __construct(?\Closure $set = null, ?\Closure $get = null)
    {
        $this->set = $set;
        $this->get = $get;
    }

    /**
     * @inerhitDoc
     */
    public function set(mixed $resource, string $attribute, GroupsCollection $groups): string
    {
        $value = $groups->map(function (Layout $group) {
            return [
                'layout'     => $group->name(),
                'key'        => $group->key(),
                'collapsed'  => $group->isCollapsed(),
                'attributes' => $group->getAttributes(),
            ];
        });

        if ($this->set) {
            call_user_func($this->set, $resource, $value, $attribute, $groups);
        } else {
            $resource->$attribute = $value;
        }

        return $value;
    }

    /**
     * @inerhitDoc
     */
    public function get(mixed $resource, string $attribute, LayoutsCollection $groups): GroupsCollection
    {
        if ($this->get) {
            $value = call_user_func($this->get, $resource, $attribute, $groups);
        } else {
            $value = $this->extractValueFromResource($resource, $attribute);
        }

        // Fail silently in case data is invalid
        if (!is_array($value)) {
            $value = [];
        }

        // Force transform arrays to objects
        $value = array_map(function ($item) {
            return is_array($item) ? (object)$item : $item;
        }, $value);

        return GroupsCollection::make($value)->map(function ($item) use ($groups, $attribute) {
            if ($item instanceof Layout) {
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

        return $value;
    }
}
