<?php

namespace NovaFlexibleContent\Nova\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Http\Requests\NovaRequest;

class ImageForFlexible extends Image
{
    use FlexibleUpdatingAttribute;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this
            ->preview(function ($value, ?string $disk, $model) {
                return Storage::disk($disk)->url($value);
            })
            ->download(function (NovaRequest $request, Model $model, ?string $disk, $value) {
                return Storage::disk($disk)->download($value);
            })
            ->delete(function (NovaRequest $request, $model, ?string $disk, $value) {
                if ($model instanceof Model) {
                    $this->flexibleSetAttribute($request, $model, null);
                }

                Storage::disk($disk)->delete($value);

                return true;
            });
    }
}
