<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Illuminate\Support\Collection as BaseCollection;
use Whitecube\NovaFlexibleContent\Contracts\LayoutInterface;

class Collection extends BaseCollection
{

    /**
     * Find a layout based on its name
     *
     * @param  string  $name
     * @return mixed
     */
    public function find($name)
    {
        return $this->first(function (LayoutInterface $layout) use ($name) {
            return $layout->name() === $name;
        });
    }
}
