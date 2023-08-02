<?php

namespace NovaFlexibleContent\Tests\Fixtures\Layouts;

use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Nova\Fields\ImageForFlexible;
use NovaFlexibleContent\Tests\Fixtures\Layouts\Feature\LinkLayout;

class TeamMemberLayout extends Layout
{
    public function fields(): array
    {
        return [
            ImageForFlexible::make('Member', 'member')
                ->prunable()
                ->nullable(),
            Flexible::make('Links', 'links')
                ->useLayout(LinkLayout::class),
        ];
    }
}
