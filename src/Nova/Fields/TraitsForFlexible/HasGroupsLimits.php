<?php

namespace NovaFlexibleContent\Nova\Fields\TraitsForFlexible;

trait HasGroupsLimits
{
    /**
     *  Set max limit of groups in field.
     */
    public function limit(int $limit = 1): static
    {
        return $this->withMeta(['limit' => $limit]);
    }
}
