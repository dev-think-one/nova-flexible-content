<?php

namespace Whitecube\NovaFlexibleContent;

use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Whitecube\NovaFlexibleContent\Commands\CreateCast;
use Whitecube\NovaFlexibleContent\Commands\CreateLayout;
use Whitecube\NovaFlexibleContent\Commands\CreatePreset;
use Whitecube\NovaFlexibleContent\Commands\CreateResolver;
use Whitecube\NovaFlexibleContent\Http\Middleware\InterceptFlexibleAttributes;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addMiddleware();

        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-flexible-content', __DIR__ . '/../dist/js/field.js');
            Nova::style('nova-flexible-content', __DIR__ . '/../dist/css/field.css');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateCast::class,
                CreateLayout::class,
                CreatePreset::class,
                CreateResolver::class,
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Adds required middleware for Nova requests.
     *
     * @return void
     */
    public function addMiddleware()
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
}
