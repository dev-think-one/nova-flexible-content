<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Illuminate\Support\Collection as BaseCollection;

class LayoutsCollection extends BaseCollection
{
    /**
     * Find a layout based on its name
     */
    public function find(string $name, mixed $default = null): mixed
    {
        return $this->first(fn (Layout $layout) => $layout->name() === $name, $default);
    }
}
