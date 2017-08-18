<?php namespace Hht\Pusher;

use Illuminate\Support\ServiceProvider;

class PusherServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerPusher();
	}

	/**
	 * Register the driver based pusher.
	 *
	 * @return void
	 */
	protected function registerPusher()
	{
		$this->registerManager();

		$this->app->singleton('pusher.launcher', function () {
			return $this->app['pusher']->launcher($this->getDefaultDriver());
		});
	}

	/**
	 * Register the pusher manager.
	 *
	 * @return void
	 */
	protected function registerManager()
	{
		$this->app->singleton('pusher', function () {
			return new PusherManager($this->app);
		});
	}

	/**
	 * Get the default push driver.
	 *
	 * @return string
	 */
	protected function getDefaultDriver()
	{
		return $this->app['config']['pushers.default'];
	}
}