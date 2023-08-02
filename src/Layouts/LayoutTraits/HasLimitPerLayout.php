<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

trait HasLimitPerLayout
{
    /**
     * The maximum amount of this layout type that can be added.
     * Can be set in custom layouts.
     */
    protected int $limit = 0;

    public function limit(): int
    {
        return $this->limit;
    }

    public function useLimit(int $limit = 0): static
    {
        $this->limit = $limit;

        return $this;
    }
}
