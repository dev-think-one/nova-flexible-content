<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

use Illuminate\Support\Str;
use Laravel\Nova\Nova;

trait HasNameAndTitle
{
    /**
     * The layout's unique identifier.
     */
    protected string $name = '';

    /**
     * The layout's human-readable title.
     */
    protected string $title = '';

    /**
     * Retrieve the layout's name (identifier)
     */
    public function name(): string
    {
        if($this->name) {
            return $this->name;
        }

        $className = class_basename(static::class);
        if($className !== 'Layout' && Str::endsWith($className, 'Layout')) {
            $className = Str::beforeLast($className, 'Layout');
        }

        return  Str::snake($className);
    }

    /**
     * Retrieve the layout's human-readable title
     */
    public function title(): string
    {
        if($this->title) {
            return $this->title;
        }

        return  Nova::humanize(Str::camel($this->name()));
    }
}
