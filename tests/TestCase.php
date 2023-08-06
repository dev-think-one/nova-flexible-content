<?php

namespace NovaFlexibleContent\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Nova\NovaCoreServiceProvider;
use NovaFlexibleContent\Tests\Fixtures\NovaServiceProvider;
use Orchestra\Testbench\Database\MigrateProcessor;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            \Inertia\ServiceProvider::class,
            NovaCoreServiceProvider::class,
            NovaServiceProvider::class,
            \NovaFlexibleContent\ServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();

        $migrator = new MigrateProcessor($this, [
            '--path'     => __DIR__.'/Fixtures/migrations',
            '--realpath' => true,
        ]);
        $migrator->up();
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

        // $app['config']->set('nova-seo-entity.some_key', 'some_value');
    }
}
