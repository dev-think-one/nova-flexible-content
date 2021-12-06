<?php

namespace Whitecube\NovaFlexibleContent\Value;

interface ResolverInterface
{
    /**
     * Set the field's value.
     *
     * @param mixed                          $model
     * @param string                         $attribute
     * @param \Illuminate\Support\Collection $groups
     * @return string
     */
    public function set($model, $attribute, $groups);

    /**
     * Get the field's value.
     *
     * @param mixed                                                    $model
     * @param string                                                   $attribute
     * @param \Whitecube\NovaFlexibleContent\Layouts\LayoutsCollection $groups
     * @return \Illuminate\Support\Collection
     */
    public function get($model, $attribute, $groups);
}
