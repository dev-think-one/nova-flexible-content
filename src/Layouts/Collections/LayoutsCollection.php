<?php

namespace NovaFlexibleContent\Layouts\Collections;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use NovaFlexibleContent\Layouts\Layout;

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

    public function whereName(string|array $names): static
    {
        $names = array_filter(array_unique(Arr::wrap($names)));

        return $this->filter(fn (Layout $layout) => in_array($layout->name(), $names));
    }

}
