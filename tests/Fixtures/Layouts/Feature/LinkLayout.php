<?php

namespace NovaFlexibleContent\Tests\Fixtures\Layouts\Feature;

use Laravel\Nova\Fields\Text;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Nova\Fields\FileForFlexible;

class LinkLayout extends Layout
{
    public function fields(): array
    {
        return [
            Text::make('Text', 'text'),
            Text::make('Link', 'link')
                ->hideFromDetail(),
            FileForFlexible::make('File', 'file')
                ->prunable()
                ->rules(['max:' . 1024 * 10])
                ->deletable(),
            FileForFlexible::make('Second File', 'second_file')
                ->prunable()
                ->rules(['max:' . 1024 * 10])
                ->deletable(),
        ];
    }
}
