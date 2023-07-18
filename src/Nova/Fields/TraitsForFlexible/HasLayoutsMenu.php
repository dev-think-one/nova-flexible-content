<?php

namespace NovaFlexibleContent\Nova\Fields\TraitsForFlexible;

trait HasLayoutsMenu
{
    /**
     * Initialise trait in Flexible constructor.
     */
    protected function initializeHasLayoutsMenu(): void
    {
        $this->useDefaultLayoutsMenu();
    }

    /**
     * Set the dropdown button configuration.
     */
    public function layoutsMenuButton(?string $buttonText = null, array $options = []): static
    {
        return $this->withMeta([
            'button'        => $buttonText,
            'buttonOptions' => $options,
        ]);
    }

    /**
     * Set custom dropdown menu component.
     */
    public function layoutsMenu(string $component = '', array $componentOptions = []): static
    {
        return $this->withMeta(['menu' => ['component' => $component, 'data' => $componentOptions]]);
    }

    /**
     * Set custom dropdown menu component.
     */
    public function useDefaultLayoutsMenu(array $componentOptions = []): static
    {
        return $this->layoutsMenu('FlexibleDefaultMenu', $componentOptions);
    }

    /**
     * Set custom dropdown menu component.
     */
    public function useSearchableLayoutsMenu(array $componentOptions = []): static
    {
        return $this->layoutsMenu('FlexibleSearchableMenu', $componentOptions);
    }

    /**
     * @deprecated
     */
    public function button(?string $label = null): static
    {
        return $this->layoutsMenuButton($label);
    }

    /**
     * @deprecated
     */
    public function menu(string $component, array $data = []): static
    {
        return $this->layoutsMenu($component, $data);
    }
}
