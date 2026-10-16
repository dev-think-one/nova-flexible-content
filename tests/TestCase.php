<?php

namespace NovaFlexibleContent\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Nova\NovaCoreServiceProvider;
use NovaFlexibleContent\Tests\Fixtures\NovaServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('nova:publish');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Inertia\ServiceProvider::class,
            NovaCoreServiceProvider::class,
            NovaServiceProvider::class,
            \NovaFlexibleContent\ServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Register migrations so RefreshDatabase runs them,
        // testbench 9+ executes "migrate:fresh" what drops tables created in defineDatabaseMigrations().
        $app->afterResolving('migrator', function ($migrator) use ($app) {
            $migrator->path($app->basePath('migrations'));
            $migrator->path(__DIR__.'/Fixtures/migrations');
        });

        // $app['config']->set('nova-seo-entity.some_key', 'some_value');
    }
}
