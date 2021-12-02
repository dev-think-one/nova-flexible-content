<?php

namespace Whitecube\NovaFlexibleContent\Nova\Collections;

use Laravel\Nova\Fields\FieldCollection as NovaFieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;

class FieldsCollectionWithFlexibleSearch extends NovaFieldCollection
{
    /**
     * @inheritDoc
     */
    public function findFieldByAttribute($attribute, $default = null)
    {
        if (str_contains($attribute, FlexibleAttribute::GROUP_SEPARATOR)) {
            $request  = resolve(NovaRequest::class);
            $resource = $request->findResourceOrFail();
            $fields   = $resource->updateFields($request);

            $attribute_parts = explode(FlexibleAttribute::GROUP_SEPARATOR, $attribute, 2);

            $groups = [];
            foreach ($fields as $field) {
                if ($field instanceof Flexible) {
                    $groups = array_merge($groups, $field->flattenGroups());
                }
            }

            foreach ($groups as $group) {
                if ($group->inUseKey() !== $attribute_parts[0]) {
                    continue;
                }

                return $group->fieldsCollection()->first(function ($field) use ($attribute_parts, $group) {
                    $field->group = $group;

                    return isset($field->attribute) &&
                           $field->attribute == $attribute_parts[1];
                }, $default);
            }
        }

        return $this->first(function ($field) use ($attribute) {
            return isset($field->attribute) &&
                   $field->attribute == $attribute;
        }, $default);
    }
}
