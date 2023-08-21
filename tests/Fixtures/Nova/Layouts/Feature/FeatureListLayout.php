<?php

namespace NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\Feature;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\Text;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Layouts\Preset;
use NovaFlexibleContent\Nova\Fields\ImageForFlexible;
use NovaFlexibleContent\Tests\Fixtures\Nova\Layouts\SimpleTextLayout;

class FeatureListLayout extends Layout
{
    protected string $name = 'feature-list';

    protected string $title = 'Feature List';

    protected function linksPreset()
    {
        return Preset::withLayouts([
            LinkLayout::class,
        ]);
    }

    protected function customOptionsPreset()
    {
        return Preset::withLayouts([
            SimpleTextLayout::class,
        ]);
    }

    public function fields(): array
    {
        return [
            Text::make('Title', 'title'),
            ImageForFlexible::make('Image', 'src')
                ->prunable()
                ->rules(['max:' . 1024 * 10])
                ->deletable(),
            Flexible::make('Links', 'links')
                ->preset($this->linksPreset()),
            Flexible::make('Custom Options', 'custom_options')
                ->preset($this->customOptionsPreset()),
        ];
    }

    public function imageLink(): Attribute
    {
        return Attribute::get(function () {
            $path    = $this->getAttribute('src');
            $storage = Storage::disk();
            if ($path && $storage->exists($path)) {
                return $storage->url($path);
            }

            return 'default.svg';
        });

    }
}
