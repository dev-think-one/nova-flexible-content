<?php

namespace Whitecube\NovaFlexibleContent\Support\Nova\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

trait FlexibleUpdatingAttribute
{

    /**
     * Currently this is bad bad bad solution.
     * But currently need think about deadline.
     * TODO: refactor and find other clever solution.
     *
     * @param NovaRequest $request
     * @param Model       $model
     * @return Model
     */
    protected function flexibleSetAttribute(NovaRequest $request, Model $model, mixed $newValue = null): Model
    {
        $groupKey = Str::before(
            $request->field,
            FlexibleAttribute::GROUP_SEPARATOR
        );
        $fieldKey = Str::after(
            $request->field,
            FlexibleAttribute::GROUP_SEPARATOR
        );

        /** @var Resource $resource */
        $resource = $request->findResourceOrFail();

        /** @var Flexible $field */
        $field = $resource->availableFields($request)
                          ->firstOrFail(function (Field $field) use ($request, $model, $groupKey) {
                              if ($field instanceof Flexible) {
                                  $field->resolve($model);

                                  /** @var Layout|null $group */
                                  $group = $field
                                      ->findGroupRecursive($groupKey);

                                  if ($group) {
                                      return true;
                                  }
                              }

                              return false;
                          });

        $field->groups()
              ->each(function (Layout $group) use ($groupKey, $fieldKey, $newValue) {
                  if ($group->key() == $groupKey) {
                      $group->setAttribute($fieldKey, $newValue);

                      return false;
                  }

                  if ($group->findGroupRecursiveAndSetAttribute($groupKey, $fieldKey, $newValue)) {
                      return false;
                  }

                  return true;
              });
        $field->reFillValue($model);

        return $model;
    }
}
