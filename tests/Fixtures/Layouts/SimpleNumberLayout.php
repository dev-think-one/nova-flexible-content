<?php

namespace NovaFlexibleContent\Tests\Fixtures\Layouts;

use Laravel\Nova\Fields\Number;
use NovaFlexibleContent\Layouts\Layout;

class SimpleNumberLayout extends Layout
{
    /**
     * Get the fields displayed by the layout.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Number::make('Order', 'order')
                ->updateRules(['max:100'])
                ->rules('required', 'min:0', 'max:999')
                ->displayUsing(fn () => 100),
        ];
    }
}
