<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

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

    public function get($key, $default = null)
    {
        return $this->getAttribute($key);
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
}
