<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use JsonSerializable;
use Laravel\Nova\Contracts\Deletable;
use Laravel\Nova\Contracts\Storable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Http\FlexibleAttribute;
use Whitecube\NovaFlexibleContent\Http\ScopedRequest;

class Layout implements LayoutInterface, JsonSerializable, ArrayAccess, Arrayable
{
    use HasAttributes;
    use HidesAttributes;
    use HasFlexible;

    /**
     * The layout's name.
     */
    protected string $name;

    /**
     * The layout's unique identifier.
     */
    protected ?string $key = null;

    /**
     * The layout's temporary identifier.
     */
    protected ?string $_key = null;

    /**
     * The layout's human-readable title.
     */
    protected string $title;

    /**
     * The layout's registered fields.
     */
    protected FieldCollection $fields;

    /**
     * The callback to be called when this layout removed.
     */
    protected $removeCallbackMethod;

    /**
     * The maximum amount of this layout type that can be added.
     * Can be set in custom layouts.
     */
    protected ?int $limit = null;

    /**
     * The parent model instance
     */
    protected ?Model $model = null;

    public function __construct(
        ?string               $title = null,
        ?string               $name = null,
        Collection|array|null $fields = null,
        ?string               $key = null,
        array                 $attributes = [],
        callable              $removeCallbackMethod = null
    ) {
        $this->title                = $title ?? $this->title();
        $this->name                 = $name  ?? $this->name();
        $this->fields               = new FieldCollection($fields ?? $this->fields());
        $this->key                  = is_null($key) ? null : $this->getProcessedKey($key);
        $this->removeCallbackMethod = $removeCallbackMethod;
        $this->setRawAttributes($this->cleanAttributes($attributes));
    }

    /**
     * Set the parent model instance
     *
     * @param Model $model
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
        return $this->fields;
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
     *
     * TODO: why it check when key is null? I think there logic error - please check in future.
     */
    public function matches(?string $key): bool
    {
        return $this->key === $key || $this->_key === $key;
    }


    public function findGroupRecursive($key)
    {
        $callback = function ($result, Field $field) use ($key) {
            if ($field instanceof Flexible) {
                return $field->findGroupRecursive($key);
            }

            return null;
        };

        $result = null;
        foreach ($this->fields as $key => $value) {
            $result = $callback($result, $value, $key);
            if ($result) {
                break;
            }
        }

        return $result;
    }

    public function findGroupRecursiveAndSetAttribute($groupKey, $fieldKey, $newValue): bool
    {
        $data = $this->getAttributes();

        function setAttribute(&$array, $groupKey, $fieldKey, $newValue)
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
                    setAttribute($array[$key], $groupKey, $fieldKey, $newValue);
                }
            }

            return null;
        }

        return (bool) setAttribute($data, $groupKey, $fieldKey, $newValue);
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
    public function duplicate(?string $key): static
    {
        return $this->duplicateAndHydrate($key);
    }

    /**
     * @inerhitDoc
     */
    public function duplicateAndHydrate(?string $key, array $attributes = []): static
    {
        $fields = $this->fields->map(function ($field) {
            return $this->cloneField($field);
        });

        $clone        = new static(
            $this->title,
            $this->name,
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
     * @param \Laravel\Nova\Fields\Field $original
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
     * @param bool $empty
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
     *
     * @param array $attributes
     * @return array
     */
    public function resolveForDisplay(array $attributes = [])
    {
        $this->fields->each(function ($field) use ($attributes) {
            $field->resolveForDisplay($attributes);
        });

        return $this->getResolvedValue();
    }

    /**
     * Filter the layout's fields for detail view
     *
     * @param NovaRequest $request
     * @param             $resource
     */
    public function filterForDetail(NovaRequest $request, $resource)
    {
        $this->fields = $this->fields->filterForDetail($request, $resource);
    }

    /**
     * Get the layout's resolved representation. Best used
     * after a resolve() call
     *
     * @return array
     */
    public function getResolvedValue()
    {
        return [
            'layout'     => $this->name,

            // The (old) temporary key is preferred to the new one during
            // field resolving because we need to keep track of the current
            // attributes during the next fill request that will override
            // the key with a new, stronger & definitive one.
            'key'        => $this->inUseKey(),

            // The layout's fields now temporarily contain the resolved
            // values from the current group's attributes. If multiple
            // groups use the same layout, the current values will be lost
            // since each group uses the same fields by reference. That's
            // why we need to serialize the field's current state.
            'attributes' => $this->fields->jsonSerialize(),
        ];
    }

    /**
     * Fill attributes using underlaying fields and incoming request
     *
     * @param ScopedRequest $request
     * @return array
     */
    public function fill(ScopedRequest $request)
    {
        return $this->fields->map(function ($field) use ($request) {
            return $field->fill($request, $this);
        })
                            ->filter(function ($callback) {
                                return is_callable($callback);
                            })
                            ->values()
                            ->all();
    }

    /**
     * Get validation rules for fields concerned by given request
     *
     * @param ScopedRequest $request
     * @param string        $specificty
     * @param string        $key
     * @return array
     */
    public function generateRules(ScopedRequest $request, $specificty, $key)
    {
        return $this->fields->map(function ($field) use ($request, $specificty, $key) {
            return $this->getScopedFieldRules($field, $request, $specificty, $key);
        })
                            ->collapse()
                            ->all();
    }

    /**
     * Get validation rules for fields concerned by given request
     *
     * @param \Laravel\Nova\Fields\Field $field
     * @param ScopedRequest              $request
     * @param null|string                $specificty
     * @param string                     $key
     * @return array
     */
    protected function getScopedFieldRules($field, ScopedRequest $request, $specificty, $key)
    {
        $method = 'get' . ucfirst($specificty) . 'Rules';

        $rules = call_user_func([$field, $method], $request);

        return collect($rules)->mapWithKeys(function ($validatorRules, $attribute) use ($key, $field) {
            $key = $key . '.attributes.' . $attribute;

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
     * TODO: $model can be Model or Layout - this is problem - so need check and change logic in future
     */
    protected function defaultRemoveCallback(Flexible $flexible, LayoutInterface $layout, NovaRequest $request, $model)
    {
        $layout->fieldsCollection()
               ->each(function (Field $field) use ($layout, $request, $model) {
                   if ($field instanceof Storable
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
     * Dynamically retrieve attributes on the layout.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the layout.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return !is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Transform empty attribute values to null
     *
     * @param array $attributes
     * @return array
     */
    protected function cleanAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            if (!is_string($value) || strlen($value)) {
                continue;
            }
            $attributes[$key] = null;
        }

        return $attributes;
    }

    /**
     * Get the attributes that should be converted to dates.
     */
    protected function getDates(): array
    {
        return $this->dates ?? [];
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat ?: 'Y-m-d H:i:s';
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        return $this->casts ?? [];
    }

    /**
     * Check if relation exists. Layouts do not have relations.
     *
     * @return bool
     */
    protected function relationLoaded()
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
            'name'   => $this->name,
            'title'  => $this->title,
            'fields' => $this->fields->jsonSerialize(),
            'limit'  => $this->limit,
        ];
    }

    /**
     * Returns an unique key for this group if it's not already the case
     *
     * @param string $key
     * @return string
     * @throws \Exception
     */
    protected function getProcessedKey($key)
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
