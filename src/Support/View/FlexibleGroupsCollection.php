<?php

namespace Whitecube\NovaFlexibleContent\Support\View;

use Illuminate\Support\Collection;

class FlexibleGroupsCollection extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);

        $items = [];
        foreach ($this->items as $item) {
            if (!empty($item['layout'])
                && !empty($item['key'])
                && isset($item['attributes'])
            ) {
                $items[] = new FlexibleGroup($item['layout'], $item['key'], is_array($item['attributes']) ? $item['attributes'] : []);
            }
        }

        $this->items = $items;
    }
}
