<?php

namespace NovaFlexibleContent\Nova\Fields;

use Illuminate\Support\Facades\Storage;

class VideoForFlexible extends FileForFlexible
{
    public $component = 'video-field';

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this
            ->preview(function ($value, ?string $disk, $model) {
                return Storage::disk($disk)->url($value);
            });
    }
}
