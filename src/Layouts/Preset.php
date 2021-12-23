<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Whitecube\NovaFlexibleContent\Flexible;

abstract class Preset
{
    protected array $layoutMapping = [];

    /**
     * @return string[]|\Whitecube\NovaFlexibleContent\Layouts\Layout[]
     */
    abstract public function usedLayouts(): array;

    public function layoutMapping(): array
    {
        if (!empty($this->layoutMapping)) {
            return $this->layoutMapping;
        }
        foreach ($this->usedLayouts() as $layout) {
            if (is_a($layout, Layout::class, true)) {
                if (is_string($layout)) {
                    $layout = new $layout;
                }
                $this->layoutMapping[$layout->name()] = $layout::class;
            }
        }

        return $this->layoutMapping;
    }

    public function handle(Flexible $field)
    {
        foreach ($this->layoutMapping() as $layout) {
            $field->addLayout($layout);
        }
    }
}
