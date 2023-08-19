<?php

namespace NovaFlexibleContent;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use NovaFlexibleContent\Http\FlexibleAttribute;
use NovaFlexibleContent\Http\Middleware\InterceptFlexibleAttributes;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->addMiddleware();

        Nova::serving(function (ServingNova $event) {
            Nova::script('flexible-content-field', __DIR__ . '/../dist/js/field.js');
            Nova::style('flexible-content-field', __DIR__ . '/../dist/css/field.css');

            Nova::provideToScript([
                'flexible-content-field.flexible-attribute-key-name' => FlexibleAttribute::REGISTER_FLEXIBLE_FIELD_NAME,
                'flexible-content-field.file-indicator-prefix'       => FlexibleAttribute::FILE_INDICATOR,
                'flexible-content-field.group-separator'             => FlexibleAttribute::GROUP_SEPARATOR,
            ]);
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/nova-flexible-content.php' => config_path('nova-flexible-content.php'),
            ], 'config');

            $this->commands([
                \NovaFlexibleContent\Console\Commands\GenerateIdeHelperLayoutsCommand::class,
            ]);
        }

        $this->registerCollectionMacros();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nova-flexible-content.php', 'nova-flexible-content');
    }

    /**
     * Adds required middleware for Nova requests.
     *
     * @return void
     */
    public function addMiddleware(): void
    {
        $router = $this->app['router'];

        if ($router->hasMiddlewareGroup('nova')) {
            $router->pushMiddlewareToGroup('nova', InterceptFlexibleAttributes::class);

            return;
        }

        if (!$this->app->configurationIsCached()) {
            config()->set('nova.middleware', array_merge(
                config('nova.middleware', []),
                [InterceptFlexibleAttributes::class]
            ));
        }
    }

    protected function registerCollectionMacros(): void
    {
        Collection::macro('isAssoc', function () {
            return Arr::isAssoc($this->toBase()->all());
        });
    }
}
