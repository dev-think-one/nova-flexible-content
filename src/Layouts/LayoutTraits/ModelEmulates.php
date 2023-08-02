<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait ModelEmulates
{
    use AttributesManipulation;

    /**
     * Define that Layout is a model, when in fact it is not.
     *
     * @var bool
     */
    protected bool $exists = false;

    /**
     * Define that Layout is a model, when in fact it is not.
     *
     * @var bool
     */
    protected bool $wasRecentlyCreated = false;

    /**
     * The relation resolver callbacks for the Layout.
     *
     * @var array
     */
    protected static array $relationResolvers = [];

    /**
     * The loaded relationships for the Layout.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * Check if relation exists.
     * Layouts do not have relations.
     */
    public function relationLoaded($key): bool
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
     * Get the dynamic relation resolver if defined or inherited, or return null.
     * Since it is not possible to define a relation on a layout, this method
     * returns null
     *
     * @param string $class
     * @param string $key
     * @return mixed
     */
    public function relationResolver($class, $key)
    {
        return null;
    }

    /**
     * Force Fill the layout with an array of attributes.
     */
    public function forceFill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $attribute = Str::replace('->', '.', $key);
            Arr::set($this->attributes, $attribute, $value);
        }

        return $this;
    }
}
