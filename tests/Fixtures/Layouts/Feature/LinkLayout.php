<?php

namespace NovaFlexibleContent\Tests\Fixtures\Layouts\Feature;

use Laravel\Nova\Fields\Text;
use NovaFlexibleContent\Layouts\Layout;

class LinkLayout extends Layout
{
    public function fields(): array
    {
        return [
            Text::make('Text', 'text'),
            Text::make('Link', 'link')
                ->hideFromDetail(),
        ];
    }
}
