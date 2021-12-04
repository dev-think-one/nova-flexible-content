<?php

namespace Whitecube\NovaFlexibleContent\Nova\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Nova\Collections\FieldsCollection;

/**
 * Trait related to nova resource.
 *
 * @extends \Laravel\Nova\ResolvesFields
 */
trait HasFlexibleField
{
    /**
     * @inerhitDoc
     * @return FieldsCollection
     */
    public function availableFields(NovaRequest $request)
    {
        return new FieldsCollection(parent::availableFields($request));
    }

    /**
     * @inerhitDoc
     * @return FieldsCollection
     */
    public function downloadableFields(NovaRequest $request)
    {
        return new FieldsCollection(parent::downloadableFields($request));
    }
}
