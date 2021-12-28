<?php

namespace NovaFlexibleContent\Layouts;

use NovaFlexibleContent\Flexible;

class Preset
{
    /**
     * @var array[string]string
     */
    protected array $layouts = [];

    /**
     * Initialise new instance using layouts set.
     */
    public static function withLayouts(array $usedLayouts = []): static
    {
        return (new static())->setLayouts($usedLayouts);
    }

    public function setLayouts(array $usedLayouts = []): static
    {
        $this->layouts = [];

        foreach ($usedLayouts as $key => $layout) {
            if (is_a($layout, Layout::class, true)) {
                if (is_string($layout)) {
                    $layout = new $layout;
                }
                $this->layouts[(is_numeric($key) || !$key) ? $layout->name() : $key] = $layout::class;
            }
        }

        return $this;
    }

    public function layouts(): array
    {
        return $this->layouts;
    }

    public function handle(Flexible $field)
    {
        foreach ($this->layouts() as $layout) {
            $field->useLayout($layout);
        }
    }
}
