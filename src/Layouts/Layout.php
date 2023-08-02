<?php

namespace NovaFlexibleContent\Layouts;

use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Support\Fluent;
use NovaFlexibleContent\Concerns\HasFlexible;
use NovaFlexibleContent\Http\ScopedRequest;
use NovaFlexibleContent\Layouts\LayoutTraits\Collapsable;
use NovaFlexibleContent\Layouts\LayoutTraits\HasFieldsCollection;
use NovaFlexibleContent\Layouts\LayoutTraits\HasFieldsRules;
use NovaFlexibleContent\Layouts\LayoutTraits\HasFlexibleFieldInLayout;
use NovaFlexibleContent\Layouts\LayoutTraits\HasGroupDescription;
use NovaFlexibleContent\Layouts\LayoutTraits\HasLayoutKey;
use NovaFlexibleContent\Layouts\LayoutTraits\HasLimitPerLayout;
use NovaFlexibleContent\Layouts\LayoutTraits\HasModel;
use NovaFlexibleContent\Layouts\LayoutTraits\HasNameAndTitle;
use NovaFlexibleContent\Layouts\LayoutTraits\HasRemoveCallback;
use NovaFlexibleContent\Layouts\LayoutTraits\ModelEmulates;

/**
 * @extends Fluent<string, mixed>
 */
class Layout extends Fluent
{
    use HidesAttributes;
    use HasFlexible;
    use Collapsable;
    use ModelEmulates;
    use HasNameAndTitle;
    use HasLayoutKey;
    use HasFieldsCollection;
    use HasGroupDescription;
    use HasLimitPerLayout;
    use HasRemoveCallback;
    use HasFlexibleFieldInLayout;
    use HasFieldsRules;
    use HasModel;

    public function __construct(
        ?string               $title = null,
        ?string               $name = null,
        Collection|array|null $fields = null,
        ?string               $key = null,
        array                 $attributes = [],
        ?\Closure             $removeCallbackMethod = null
    ) {
        // Override properties or set default provided ny developer
        $this->title                = $title ?: $this->title;
        $this->name                 = $name ?: $this->name;
        $this->fields               = FieldCollection::make($fields ?: $this->fields());
        $this->removeCallbackMethod = $removeCallbackMethod;

        $this->key = is_null($key) ? null : $this->generateValidLayoutKey($key);
        $this->setRawAttributes($this->setEmptyValuesToNull($attributes));
    }

    public static function make(...$args): static
    {
        return new static(...$args);
    }

    /**
     * Resolve and return the result
     *
     * @return array
     */
    public function getResolved(): array
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
        $fields = $this->fieldsCollection()->map(function ($field) {
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
        $clone->useLimit($this->limit());
        $clone->setModel($this->model);

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
     * @return static
     */
    public function resolve(bool $empty = false): static
    {
        $this->fieldsCollection()->each(function ($field) use ($empty) {
            $field->resolve($empty ? $this->duplicate($this->inUseKey()) : $this);
        });

        return $this;
    }

    /**
     * Resolve fields for display using given attributes.
     */
    public function resolveForDisplay(array $attributes = []): array
    {
        $this->fieldsCollection()->each(function ($field) use ($attributes) {
            $field->resolveForDisplay($attributes);
        });

        return $this->getResolvedValue();
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
            'attributes' => $this->fieldsCollection()->jsonSerialize(),
        ];
    }

    /**
     * Fill attributes using underlaying fields and incoming request.
     */
    public function fillFromRequest(ScopedRequest $request): array
    {
        return $this->fieldsCollection()->map(fn (Field $field) => $field->fill($request, $this))
            ->filter(fn ($callback) => is_callable($callback))
            ->values()
            ->all();
    }

    /**
     * Transform empty attribute values to null.
     */
    protected function setEmptyValuesToNull(array $dataArray = []): array
    {
        foreach ($dataArray as $key => $value) {
            if (!is_string($value) || strlen($value) > 0) {
                continue;
            }
            $dataArray[$key] = null;
        }

        return $dataArray;
    }

    /**
     * Transform layout for serialization.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        // Calling an empty "resolve" first in order to empty all fields
        $this->resolve(true);

        return [
            'name'    => $this->name(),
            'title'   => $this->title(),
            'fields'  => $this->fieldsCollection()->jsonSerialize(),
            'limit'   => $this->limit(),
            'configs' => [
                'fieldUsedForDescription' => $this->fieldUsedForDescription(),
            ],
        ];
    }
}
