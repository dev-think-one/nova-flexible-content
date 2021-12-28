<?php

namespace NovaFlexibleContent\Layouts;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;

trait AttributesManipulation
{
    use HasAttributes;

    /**
     * Convert the model instance to an array.
     */
    public function toArray(): array
    {
        return $this->attributesToArray();
    }

    /**
     * Determine if the given attribute exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return !is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     */
    public function __isset(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically retrieve attributes on the layout.
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the layout.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Unset an attribute on the model.
     */
    public function __unset(string $key): void
    {
        $this->offsetUnset($key);
    }
}
