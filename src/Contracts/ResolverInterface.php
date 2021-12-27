<?php

namespace NovaFlexibleContent\Contracts;

use NovaFlexibleContent\Layouts\GroupsCollection;
use NovaFlexibleContent\Layouts\LayoutsCollection;

interface ResolverInterface
{
    /**
     * Get the field's value.
     */
    public function get(mixed $model, string $attribute, LayoutsCollection $groups): GroupsCollection;

    /**
     * Set the field's value.
     */
    public function set(mixed $model, string $attribute, GroupsCollection $groups): string;
}
