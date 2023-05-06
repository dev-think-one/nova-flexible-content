<?php

namespace NovaFlexibleContent\Nova\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Http\Requests\NovaRequest;

class FileForFlexible extends File
{
    use FlexibleUpdatingAttribute;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this
            ->download(function (NovaRequest $request, Model $model, ?string $disk, $value) {
                return $value ? Storage::disk($disk)->download($value) : null;
            })
            ->delete(function (NovaRequest $request, $model, ?string $disk, $value) {
                if ($model instanceof Model) {
                    $this->flexibleSetAttribute($request, $model, null);
                }

                if($value) {
                    Storage::disk($disk)->delete($value);
                }

                return true;
            });
    }
}
