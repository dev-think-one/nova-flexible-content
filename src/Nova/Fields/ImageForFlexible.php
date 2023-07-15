<?php

namespace NovaFlexibleContent\Nova\Fields;

use Laravel\Nova\Fields\Image;

class ImageForFlexible extends Image
{
    use FlexibleUpdatingAttribute;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->preview($this->defaultPreviewCallback())
            ->download($this->defaultDownloadCallback())
            ->delete($this->defaultDeleteCallback());
    }
}
