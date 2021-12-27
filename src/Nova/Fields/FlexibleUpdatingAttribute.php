<?php

namespace NovaFlexibleContent\Nova\Fields;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Http\FlexibleAttribute;

trait FlexibleUpdatingAttribute
{

    /**
     * Currently this is bad bad bad solution.
     * But currently need think about deadline.
     * TODO: refactor and find other clever solution.
     *
     * @param NovaRequest $request
     * @param Model       $model
     * @param mixed|null  $newValue
     * @return Model
     */
    protected function flexibleSetAttribute(NovaRequest $request, Model $model, mixed $newValue = null): Model
    {
        if (str_contains($request->route('field'), FlexibleAttribute::GROUP_SEPARATOR)) {
            [$groupKey, $fieldKey] = explode(FlexibleAttribute::GROUP_SEPARATOR, $request->field, 2);

            /** @var Flexible $field */
            $request->findResourceOrFail()
                    ->availableFields($request)
                    ->each(function (Field $field) use ($model, $groupKey, $fieldKey, $newValue) {
                        if ($field instanceof Flexible) {
                            $field->resolve($model);
                            if ($field->setAttributeRecursive($groupKey, $fieldKey, $newValue)) {
                                $field->reFillValue($model);

                                // Break loop
                                return false;
                            }
                        }
                    });
        }

        return $model;
    }
}
