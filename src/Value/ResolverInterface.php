<?php

namespace Whitecube\NovaFlexibleContent\Value;

interface ResolverInterface
{
    public function set($model, $attribute, $groups);
    public function get($model, $attribute, $groups);
}
