<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Whitecube\NovaFlexibleContent\Flexible;

class Preset
{
    protected array $layoutMapping = [];

    /**
     * @var string[]|\Whitecube\NovaFlexibleContent\Layouts\Layout[]
     */
    protected array $usedLayouts = [];

    public static function withLayouts(array $usedLayouts = []): static
    {
        return (new static())->useLayouts($usedLayouts);
    }

    public function useLayouts(array $usedLayouts = []): static
    {
        $this->usedLayouts = $usedLayouts;

        return $this;
    }

    public function usedLayouts(): array
    {
        return $this->usedLayouts;
    }

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
