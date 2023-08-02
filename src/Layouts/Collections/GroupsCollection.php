<?php

namespace NovaFlexibleContent\Layouts\Collections;

use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Layouts\Layout;

/**
 * @extends  \Illuminate\Support\Collection<int, \NovaFlexibleContent\Layouts\Layout>
 */
class GroupsCollection extends Collection
{
    public function fireRemoveCallback(Flexible $flexibleField, NovaRequest $request, $model): static
    {
        return $this->each(function (Layout $layout) use ($flexibleField, $request, $model) {
            $layout->fireRemoveCallback($flexibleField, $request, $model);
        });
    }
}
