<?php

namespace NovaFlexibleContent\Layouts;

use Illuminate\Support\Collection as BaseCollection;

/**
 * @extends  \Illuminate\Support\Collection<int, \NovaFlexibleContent\Layouts\Layout>
 */
class LayoutsCollection extends BaseCollection
{
    /**
     * Find a layout based on its name
     *
     * @return \NovaFlexibleContent\Layouts\Layout|mixed
     */
    public function find(string $name, mixed $default = null): mixed
    {
        return $this->first(fn (Layout $layout) => $layout->name() === $name, $default);
    }
}
