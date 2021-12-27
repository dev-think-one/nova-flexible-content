<?php

namespace Whitecube\NovaFlexibleContent\Contracts;

use Whitecube\NovaFlexibleContent\Layouts\GroupsCollection;
use Whitecube\NovaFlexibleContent\Layouts\LayoutsCollection;

interface ResolverInterface
{
    /**
     * Set the field's value.
     */
    public function set(mixed $model, string $attribute, GroupsCollection $groups): string;

    /**
     * Get the field's value.
     */
    public function get(mixed $model, string $attribute, LayoutsCollection $groups): GroupsCollection;
}
