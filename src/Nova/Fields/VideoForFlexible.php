<?php

namespace NovaFlexibleContent\Nova\Fields;

class VideoForFlexible extends \NovaVideoField\Video
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
