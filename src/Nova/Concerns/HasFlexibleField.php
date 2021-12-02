<?php

namespace Whitecube\NovaFlexibleContent\Nova\Concerns;

use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Nova\Collections\FieldsCollectionWithFlexibleSearch;

trait HasFlexibleField
{
    /**
     * Get the fields that are available for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function availableFields(NovaRequest $request)
    {
        return new FieldsCollectionWithFlexibleSearch(array_values($this->filter($this->fields($request))));
    }

    public function downloadableFields(NovaRequest $request)
    {
        return new FieldsCollectionWithFlexibleSearch(parent::downloadableFields($request)->toArray());
    }
}
