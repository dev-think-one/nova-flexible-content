<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Illuminate\Support\Collection as BaseCollection;
use Whitecube\NovaFlexibleContent\Contracts\LayoutInterface;

class LayoutsCollection extends BaseCollection
{

    /**
     * Find a layout based on its name
     */
    public function find(string $name, mixed $default = null): mixed
    {
        return $this->first(fn (LayoutInterface $layout) => $layout->name() === $name, $default);
    }
}
