<?php

namespace Whitecube\NovaFlexibleContent\Nova\Collections;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection as NovaFieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;

class FieldsCollection extends NovaFieldCollection
{
    /**
     * @inheritDoc
     */
    public function findFieldByAttribute($attribute, $default = null)
    {
        if (str_contains($attribute, FlexibleAttribute::GROUP_SEPARATOR)) {
            return $this->findFieldUsedInFlexibleByAttribute($attribute, $default);
        }

        return $this->first(function ($field) use ($attribute) {
            return isset($field->attribute) &&
                   $field->attribute == $attribute;
        }, $default);
    }

    public function findFieldUsedInFlexibleByAttribute($attribute, mixed $default = null)
    {
        $request = resolve(NovaRequest::class);

        $resource = $request->findResourceOrFail();

        [$groupKey, $fieldKey] = explode(FlexibleAttribute::GROUP_SEPARATOR, $attribute, 2);

        foreach ($resource->updateFields($request) as $field) {
            if ($field instanceof Flexible) {
                if ($group = $field->findGroupRecursive($groupKey)) {
                    $foundField = $group->fieldsCollection()
                                           ->first(fn (Field $groupField) => $groupField->attribute == $fieldKey);
                    if ($foundField) {
                        return $foundField;
                    }
                }
            }
        }

        return $default;
    }
}
