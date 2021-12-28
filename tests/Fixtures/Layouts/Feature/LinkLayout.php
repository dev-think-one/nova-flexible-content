<?php

namespace NovaFlexibleContent\Tests\Fixtures\Layouts\Feature;

use Laravel\Nova\Fields\Text;
use NovaFlexibleContent\Layouts\Layout;

class LinkLayout extends Layout
{
    protected string $name = 'link';

    protected string $title = 'Link';

    public function fields(): array
    {
        return [
            Text::make('Text', 'text'),
            Text::make('Link', 'link'),
        ];
    }
}
