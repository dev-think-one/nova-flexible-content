<?php

namespace Whitecube\NovaFlexibleContent\Layouts;

use Whitecube\NovaFlexibleContent\Flexible;

class Preset
{
    /**
     * @var array[string]string
     */
    protected array $_layoutMapping = [];

    /**
     * @var string[]|\Whitecube\NovaFlexibleContent\Layouts\Layout[]
     */
    protected array $layouts = [];

    public static function withLayouts(array $usedLayouts = []): static
    {
        return (new static())->setLayouts($usedLayouts);
    }

    public function setLayouts(array $usedLayouts = []): static
    {
        $this->layouts = $usedLayouts;

        return $this;
    }

    public function layouts(): array
    {
        return $this->layouts;
    }

    public function layoutMapping(): array
    {
        if (!empty($this->_layoutMapping)) {
            return $this->_layoutMapping;
        }
        foreach ($this->layouts() as $layout) {
            if (is_a($layout, Layout::class, true)) {
                if (is_string($layout)) {
                    $layout = new $layout;
                }
                $this->_layoutMapping[$layout->name()] = $layout::class;
            }
        }

        return $this->_layoutMapping;
    }

    public function handle(Flexible $field)
    {
        foreach ($this->layoutMapping() as $layout) {
            $field->useLayout($layout);
        }
    }
}
