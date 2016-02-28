<?php

namespace Siganurame\Repositories;

use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Siganurame\Repositories\Support\RepositoryGenerator;
use Siganurame\Repositories\Console\Commands\MakeRepositoryCommand;

class RepositoriesServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
     * Perform post-registration booting of services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * 
     * @return void
     */
    public function boot()
    {
		$this->publishes([
			__DIR__.'/../../config/repository.php' => config_path('repository.php')
		], 'config');
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Register bindings.
        $this->registerBindings();

        // Register make repository command.
        $this->registerMakeRepositoryCommand();

        // Register commands
        $this->commands('command.repository.make');
	}

	/**
     * Register the bindings.
     *
     * @return void
     */
    protected function registerBindings()
    {
        // FileSystem.
        $this->app->instance('FileSystem', new Filesystem());

        // Composer.
        $this->app->bind('Composer', function ($app)
        {
            return new Composer($app['FileSystem']);
        });

        // Repository creator.
        $this->app->singleton('RepositoryGenerator', function ($app)
        {
            return new RepositoryGenerator($app['FileSystem']);
        });
    }

    /**
     * Register the make:repository command.
     *
     * @return void
     */
    protected function registerMakeRepositoryCommand()
    {
        // Make repository command.
        $this->app['command.repository.make'] = $this->app->share(
            function($app) {
                return new MakeRepositoryCommand($app['RepositoryGenerator'], $app['Composer']);
            }
        );
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['command.repository.make'];
	}

}
