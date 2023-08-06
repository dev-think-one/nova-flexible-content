<?php

namespace NovaFlexibleContent\Value;

use NovaFlexibleContent\Layouts\Collections\GroupsCollection;
use NovaFlexibleContent\Layouts\Collections\LayoutsCollection;

interface Resolver
{
    /**
     * Get the field's value.
     */
    public function get(mixed $resource, string $attribute, LayoutsCollection $groups): GroupsCollection;

    /**
     * Set the field's value.
     */
    public function set(mixed $resource, string $attribute, GroupsCollection $groups): string;
}
