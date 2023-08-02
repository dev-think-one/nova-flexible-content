<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use NovaFlexibleContent\Http\FlexibleAttribute;
use NovaFlexibleContent\Http\ScopedRequest;

trait HasFieldsRules
{
    /**
     * Get validation rules for fields concerned by given request.
     */
    public function generateRules(ScopedRequest $request, string $key, ?string $type = null): array
    {
        return $this->fieldsCollection()->map(fn ($field) => $this->getScopedFieldRules($field, $request, $key, $type))
            ->collapse()
            ->all();
    }

    /**
     * Get validation rules for fields concerned by given request.
     */
    protected function getScopedFieldRules(Field $field, ScopedRequest $request, string $key, ?string $type = null): array
    {
        $type   = Str::ucfirst($type);
        $method = "get{$type}Rules";

        $rules = [];
        if(method_exists($field, $method)) {
            $rules = call_user_func([$field, $method], $request);
        }

        return collect($rules)
            ->mapWithKeys(function ($validatorRules, $attribute) use ($key, $field, $request) {
                $key = $request->isFileAttribute($attribute)
                    ? $request->getFileAttribute($attribute)
                    : "{$key}.attributes.{$attribute}";

                return [$key => $this->wrapScopedFieldRules($field, $validatorRules)];
            })
            ->filter()
            ->all();
    }

    /**
     * Wrap the rules in an array containing field information for later use.
     */
    protected function wrapScopedFieldRules(Field $field, array $rules = []): array
    {
        if (is_a($rules['attribute'] ?? null, FlexibleAttribute::class)) {
            return $rules;
        }

        return [
            'attribute' => FlexibleAttribute::make($field->attribute, $this->inUseKey()),
            'rules'     => $rules,
        ];
    }
}
