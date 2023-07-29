<?php

namespace NovaFlexibleContent\Nova\Fields\TraitsForFlexible;

use NovaFlexibleContent\Layouts\Preset;

trait HasPreset
{
    /**
     * Apply a field configuration preset.
     */
    public function preset(Preset|string|array $preset): static
    {
        if (is_string($preset)) {
            $preset = new $preset;
        } elseif (is_array($preset)) {
            $preset = Preset::withLayouts($preset);
        }

        $preset->handle($this);

        return $this;
    }
}
