<?php

namespace NovaFlexibleContent\Layouts;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use JsonSerializable;
use Laravel\Nova\Contracts\Deletable;
use Laravel\Nova\Contracts\Storable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaFlexibleContent\Concerns\HasFlexible;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Http\FlexibleAttribute;
use NovaFlexibleContent\Http\ScopedRequest;

class Layout implements JsonSerializable, ArrayAccess, Arrayable
{
    use AttributesManipulation;
    use HidesAttributes;
    use HasFlexible;
    use Collapsable;

    /**
     * The layout's unique identifier.
     */
    protected string $name = '';

    /**
     * The layout's human-readable title.
     */
    protected string $title = '';

    /**
     * The layout's unique identifier.
     */
    protected ?string $key = null;

    /**
     * The layout's temporary identifier.
     */
    protected ?string $_key = null;

    /**
     * The layout's registered fields.
     */
    protected ?FieldCollection $fields = null;

    /**
     * The callback to be called when this layout removed.
     */
    protected $removeCallbackMethod;

    /**
     * The maximum amount of this layout type that can be added.
     * Can be set in custom layouts.
     */
    protected int $limit = 0;

    /**
     * The parent model instance
     */
    protected Model|Layout|null $model = null;

    public function __construct(
        ?string               $title = null,
        ?string               $name = null,
        Collection|array|null $fields = null,
        ?string               $key = null,
        array                 $attributes = [],
        callable              $removeCallbackMethod = null
    ) {
        $this->fields               = FieldCollection::make($fields ?? $this->fields());
        $this->title                = $title ?? $this->title();
        $this->name                 = $name  ?? $this->name();
        $this->key                  = is_null($key) ? null : $this->getProcessedKey($key);
        $this->removeCallbackMethod = $removeCallbackMethod;
        $this->setRawAttributes($this->setEmptyValuesToNull($attributes));
    }

    /**
     * Set the parent model instance
     *
     * @param  Model  $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Retrieve the layout's name (identifier)
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Retrieve the layout's title
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Retrieve the layout's fields as array.
     */
    public function fields(): array
    {
        return $this->fields ? $this->fields->all() : [];
    }

    /**
     * Retrieve the layout's fields as a collection.
     */
    public function fieldsCollection(): FieldCollection
    {
        return $this->fields ?? FieldCollection::make();
    }


    /**
     * Retrieve the layout's unique key
     */
    public function key(): ?string
    {
        return $this->key;
    }

    /**
     * Retrieve the key currently in use in the views.
     */
    public function inUseKey(): ?string
    {
        return $this->_key ?? $this->key();
    }

    /**
     * Check if this group matches the given key.
     */
    public function isUseKey(string $groupKey): bool
    {
        return $this->key === $groupKey || $this->_key === $groupKey;
    }

    public function findFlexibleGroupRecursive(string $groupKey): ?static
    {
        foreach ($this->fields as $field) {
            if ($field instanceof Flexible) {
                if ($group = $field->findGroupRecursive($groupKey)) {
                    return $group;
                }
            }
        }

        return null;
    }

    /**
     * TODO: rebuild
     */
    public function findGroupRecursiveAndSetAttribute($groupKey, $fieldKey, $newValue): bool
    {
        $data = $this->getAttributes();

        return (bool) $this->setAttributeInternalCallback($data, $groupKey, $fieldKey, $newValue);
    }

    public function setAttributeInternalCallback(&$array, $groupKey, $fieldKey, $newValue)
    {
        foreach ($array as $key => $value) {
            if (is_object($value)
                && $value->key === $groupKey
                && is_object($value->attributes)) {
                foreach ($value->attributes as $attribute => $attrValue) {
                    if ($attribute === $fieldKey) {
                        $value->attributes->$attribute = $newValue;

                        return $array;
                    }
                }
            }
            if (is_array($value)) {
                $this->setAttributeInternalCallback($array[$key], $groupKey, $fieldKey, $newValue);
            }
        }

        return null;
    }

    /**
     * Resolve and return the result
     *
     * @return array
     */
    public function getResolved()
    {
        $this->resolve();

        return $this->getResolvedValue();
    }

    /**
     * Resolve the field for display and return the result.
     *
     * @return array
     */
    public function getResolvedForDisplay()
    {
        return $this->resolveForDisplay($this->getAttributes());
    }

    /**
     * @inerhitDoc
     */
    public function duplicate(?string $key, array $attributes = []): static
    {
        $fields = $this->fields->map(function ($field) {
            return $this->cloneField($field);
        });

        $clone = new static(
            $this->title(),
            $this->name(),
            $fields,
            $key,
            $attributes,
            $this->removeCallbackMethod,
        );
        $clone->limit = $this->limit;
        if (!is_null($this->model)) {
            $clone->setModel($this->model);
        }

        return $clone;
    }

    /**
     * Create a working field clone instance
     *
     * @param  \Laravel\Nova\Fields\Field  $original
     * @return \Laravel\Nova\Fields\Field
     */
    protected function cloneField(Field $original)
    {
        $field = clone $original;

        $callables = ['displayCallback', 'resolveCallback', 'fillCallback', 'requiredCallback'];

        foreach ($callables as $callable) {
            if (!is_a($field->$callable ?? null, \Closure::class)) {
                continue;
            }
            $field->$callable = $field->$callable->bindTo($field);
        }

        return $field;
    }

    /**
     * Resolve fields using given attributes.
     *
     * @param  bool  $empty
     * @return void
     */
    public function resolve($empty = false)
    {
        $this->fields->each(function ($field) use ($empty) {
            $field->resolve($empty ? $this->duplicate($this->inUseKey()) : $this);
        });
    }

    /**
     * Resolve fields for display using given attributes.
     */
    public function resolveForDisplay(array $attributes = []): array
    {
        $this->fields->each(function ($field) use ($attributes) {
            $field->resolveForDisplay($attributes);
        });

        return $this->getResolvedValue();
    }

    /**
     * Filter the layout's fields for detail view.
     */
    public function filterForDetail(NovaRequest $request, mixed $resource): static
    {
        $this->fields = $this->fields->filterForDetail($request, $resource);

        return $this;
    }

    /**
     * Get the layout's resolved representation. Best used
     * after a resolve() call
     */
    public function getResolvedValue(): array
    {
        return [
            'layout' => $this->name(),

            'collapsed' => $this->isCollapsed(),

            // The (old) temporary key is preferred to the new one during
            // field resolving because we need to keep track of the current
            // attributes during the next fill request that will override
            // the key with a new, stronger & definitive one.
            'key' => $this->inUseKey(),

            // The layout's fields now temporarily contain the resolved
            // values from the current group's attributes. If multiple
            // groups use the same layout, the current values will be lost
            // since each group uses the same fields by reference. That's
            // why we need to serialize the field's current state.
            'attributes' => $this->fields->jsonSerialize(),
        ];
    }

    /**
     * Fill attributes using underlaying fields and incoming request.
     */
    public function fill(ScopedRequest $request): array
    {
        return $this->fields->map(fn ($field)       => $field->fill($request, $this))
                            ->filter(fn ($callback) => is_callable($callback))
                            ->values()
                            ->all();
    }

    /**
     * Get validation rules for fields concerned by given request.
     */
    public function generateRules(ScopedRequest $request, ?string $specificty, string $key): array
    {
        return $this->fields->map(fn ($field) => $this->getScopedFieldRules($field, $request, $specificty, $key))
                            ->collapse()
                            ->all();
    }

    /**
     * Get validation rules for fields concerned by given request.
     */
    protected function getScopedFieldRules(Field $field, ScopedRequest $request, ?string $specificty, string $key): array
    {
        $method = 'get'.ucfirst($specificty).'Rules';

        $rules = call_user_func([$field, $method], $request);

        return collect($rules)->mapWithKeys(function ($validatorRules, $attribute) use ($key, $field) {
            $key = $key.'.attributes.'.$attribute;

            return [$key => $this->wrapScopedFieldRules($field, $validatorRules)];
        })
                              ->filter()
                              ->all();
    }

    /**
     * The method to call when this layout removed.
     */
    public function fireRemoveCallback(Flexible $flexible, NovaRequest $request, $model)
    {
        $arguments = [$flexible, $this, $request, $model];
        if (is_callable($this->removeCallbackMethod)) {
            return call_user_func_array($this->removeCallbackMethod, $arguments);
        }

        return $this->defaultRemoveCallback(...$arguments);
    }

    /**
     * The default behaviour when removed.
     *
     * TODO: confusing code - should be reworked
     */
    protected function defaultRemoveCallback(Flexible $flexible, Layout $layout, NovaRequest $request, $model)
    {
        $layout->fieldsCollection()
               ->each(function (Field $field) use ($layout, $request, $model) {
                   if ($field instanceof Flexible) {
                       $field->resolve($layout);
                       $this->callRemoveCallbackToFlexible($field, $request, $model);
                   } elseif ($field instanceof Storable
                             && $field instanceof Deletable
                             && property_exists($field, 'deleteCallback')
                   ) {
                       if ($field->isPrunable()) {
                           $field->value = $layout->getAttribute($field->attribute);
                           call_user_func(
                               $field->deleteCallback,
                               $request,
                               $model,
                               $field->getStorageDisk(),
                               $field->getStoragePath()
                           );
                       }
                   }
               });
    }

    public function callRemoveCallbackToFlexible(Flexible $field, NovaRequest $request, $model)
    {
        $field->groups()->each(function (Layout $layout) use ($field, $request, $model) {
            $layout->fireRemoveCallback($field, $request, $model);
        });
    }

    /**
     * Wrap the rules in an array containing field information for later use
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

    /**
     * Transform empty attribute values to null
     */
    protected function setEmptyValuesToNull(array $dataArray = []): array
    {
        foreach ($dataArray as $key => $value) {
            if (!is_string($value) || strlen($value)) {
                continue;
            }
            $dataArray[$key] = null;
        }

        return $dataArray;
    }

    /**
     * Check if relation exists.
     * Layouts do not have relations.
     */
    protected function relationLoaded($key): bool
    {
        return false;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     * Layouts do not have increment identifier.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Determine if the model uses timestamps.
     * Layouts do not use timestamps.
     */
    public function usesTimestamps(): bool
    {
        return false;
    }

    /**
     * Transform layout for serialization
     *
     * @return array
     */
    public function jsonSerialize()
    {
        // Calling an empty "resolve" first in order to empty all fields
        $this->resolve(true);

        return [
            'name'   => $this->name(),
            'title'  => $this->title(),
            'fields' => $this->fields->jsonSerialize(),
            'limit'  => $this->limit,
        ];
    }

    /**
     * Returns an unique key for this group if it's not already the case
     *
     * @throws \Exception
     */
    protected function getProcessedKey(string $key): string
    {
        if (strpos($key, '-') === false && strlen($key) === 16) {
            return $key;
        }

        // The key is either generated by Javascript or not strong enough.
        // Before assigning a new valid key we'll keep track of this one
        // in order to keep it usable in a Flexible::findGroup($key) search.
        $this->_key = $key;

        if (function_exists('random_bytes')) {
            $bytes = random_bytes(ceil(16 / 2));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(ceil(16 / 2));
        } else {
            throw new \Exception('No cryptographically secure random function available');
        }

        return substr(bin2hex($bytes), 0, 16);
    }

    /**
     * Convert the model instance to an array.
     */
    public function toArray(): array
    {
        return $this->attributesToArray();
    }
}
