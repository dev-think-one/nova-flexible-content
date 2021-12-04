<?php

namespace Whitecube\NovaFlexibleContent\Support\View;

use Illuminate\Support\Arr;

class FlexibleGroup
{
    protected string $layout;
    protected string $key;
    protected array $attributes;

    public function __construct(string $layout, string $key, array $attributes = [])
    {
        $this->layout     = $layout;
        $this->key        = $key;
        $this->attributes = $attributes;
    }

    public function layout(): string
    {
        return $this->layout;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function getAttribute(mixed $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->attributes;
        }

        return Arr::get($this->attributes, $key, $default);
    }

    public function collectFlexibleAttribute(mixed $key): FlexibleGroupsCollection
    {
        return new FlexibleGroupsCollection(Arr::get($this->attributes, $key));
    }
}
