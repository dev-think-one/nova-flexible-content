<?php

namespace NovaFlexibleContent\Tests\Fixtures\Layouts\Feature;

use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\Text;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Layouts\Preset;
use NovaFlexibleContent\Nova\Fields\ImageForFlexible;

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

    public function fields(): array
    {
        return [
            Text::make('Title', 'title'),
            ImageForFlexible::make('Image', 'src')
                            ->prunable()
                            ->rules(['max:'. 1024 * 10])
                            ->deletable(),
            Flexible::make('Links', 'links')
                    ->preset($this->linksPreset())
                    ->layoutsMenuButton('Add link'),
        ];
    }

    public function getLinksAttribute()
    {
        return $this->flexible('links', $this->linksPreset()->layouts());
    }

    public function getImageLinkAttribute()
    {
        $path    = $this->getAttribute('src');
        $storage = Storage::disk();
        if ($path && $storage->exists($path)) {
            return $storage->url($path);
        }

        return 'default.svg';
    }
}
