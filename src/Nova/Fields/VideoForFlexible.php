<?php

namespace NovaFlexibleContent\Nova\Fields;

class VideoForFlexible extends FileForFlexible
{
    public $component = 'video-field';

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->preview($this->defaultPreviewCallback());
    }
}
