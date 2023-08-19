<?php

namespace NovaFlexibleContent\Tests\Fixtures\Nova\Layouts;

use Laravel\Nova\Fields\Text;
use NovaFlexibleContent\Layouts\Layout;

class SimpleTextLayout extends Layout
{
    protected string $name = 'simple-text';

    protected string $title = 'Simple Text Layout';

    /**
     * Get the fields displayed by the layout.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Text::make('Slug', 'slug')
                ->rules('required', 'max:50'),
        ];
    }
}
