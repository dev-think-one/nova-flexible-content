<?php

namespace NovaFlexibleContent\Layouts;

trait ModelEmulates
{
    use AttributesManipulation;

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
}
