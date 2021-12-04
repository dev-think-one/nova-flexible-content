<?php

namespace Whitecube\NovaFlexibleContent\Contracts;

use Laravel\Nova\Fields\FieldCollection;
use Whitecube\NovaFlexibleContent\Http\ScopedRequest;

interface LayoutInterface
{
    public function name();

    public function title();

    public function fields(): array;

    public function fieldsCollection(): FieldCollection;

    public function key();

    public function getResolved();

    public function resolve($empty = false);

    public function fill(ScopedRequest $request);

    /**
     * Get an empty cloned instance.
     */
    public function duplicate(string $key): static;

    /**
     * Get a cloned instance with set values.
     */
    public function duplicateAndHydrate(string $key, array $attributes = []) : static;
}
