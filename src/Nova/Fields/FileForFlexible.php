<?php

namespace NovaFlexibleContent\Nova\Fields;

use Laravel\Nova\Fields\File;

class FileForFlexible extends File
{
    use FlexibleUpdatingAttribute;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->download($this->defaultDownloadCallback())
            ->delete($this->defaultDeleteCallback());
    }
}
