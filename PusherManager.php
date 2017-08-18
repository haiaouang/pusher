<?php namespace Hht\Pusher;

use Closure;
use InvalidArgumentException;
use Illuminate\Support\Arr;
use Hht\MiPush\Pusher as MiPusher;
use Hht\MiPush\PusherInterface;
use Hht\MiPush\AdapterInterface;
use Hht\MiPush\PusherAdapter as MiPusherAdapter;
use Hht\MiPush\PusherClient as MiPusherClient;
use Hht\Support\Contracts\Pusher;
use Hht\Support\Contracts\Factory as FactoryContract;

class PusherManager implements FactoryContract
{
	/**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved pusher drivers.
     *
     * @var array
     */
    protected $launchers = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new pusher manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

	/**
     * Get a pusher instance.
     *
     * @param  string  $name
     * @return \Hht\Support\Contracts\Pusher
     */
    public function drive($name = null)
    {
        return $this->launcher($name);
    }

	 /**
     * Get a pusher instance.
     *
     * @param  string  $name
     * @return \Hht\Support\Contracts\Pusher
     */
    public function launcher($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->launchers[$name] = $this->get($name);
    }


	/**
     * Attempt to get the launcher.
     *
     * @param  string  $name
     * @return \Hht\Support\Contracts\Pusher
     */
    protected function get($name)
    {
        return isset($this->launchers[$name]) ? $this->launchers[$name] : $this->resolve($name);
    }

	/**
     * Resolve the given launcher.
     *
     * @param  string  $name
     * @return \Hht\Support\Contracts\Pusher
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }

        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

	/**
     * Call a custom driver creator.
     *
     * @param  array  $config
     * @return \Hht\Support\Contracts\Pusher
     */
    protected function callCustomCreator(array $config)
    {
        $driver = $this->customCreators[$config['driver']]($this->app, $config);

        if ($driver instanceof Pusher) {
            return $this->adapt($driver);
        }

        return $driver;
    }
	
	/**
     * Create an instance of the mipush driver.
     *
     * @param  array  $config
     * @return \Hht\Support\Contracts\Pusher
     */
	public function createMipushDriver(array $config)
	{
		$mipushConfig = $this->formatMipushConfig($config);

        return $this->adapt($this->createPusher(
            new MiPusherAdapter(new MiPusherClient($mipushConfig)), $config
        ));
	}
	
	/**
     * Format the given mipush configuration with the default options.
     *
     * @param  array  $config
     * @return array
     */
	protected function formatMipushConfig(array $config)
	{
		return $config;
	}

	/**
     * Create a Pusher instance with the given adapter.
     *
     * @param  \Hht\MiPush\AdapterInterface  $adapter
     * @param  array  $config
     * @return \Hht\MiPush\PusherInterface
     */
    protected function createPusher(AdapterInterface $adapter, array $config)
    {
        return new MiPusher($adapter, count($config) > 0 ? $config : null);
    }

	/**
     * Adapt the pusher implementation.
     *
     * @param  \Hht\MiPush\PusherInterface  $pusher
     * @return \Hht\Support\Contracts\Pusher
     */
    protected function adapt(PusherInterface $pusher)
    {
        return new PusherAdapter($pusher);
    }

	/**
     * Set the given launcher instance.
     *
     * @param  string  $name
     * @param  mixed  $launcher
     * @return void
     */
    public function set($name, $launcher)
    {
        $this->launchers[$name] = $launcher;
    }

	/**
     * Get the pusher connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["pushers.launchers.{$name}"];
    }

	/**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['pushers.default'];
    }

	/**
     * Register a custom driver creator Closure.
     *
     * @param  string    $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

	/**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->launcher()->$method(...$parameters);
    }
}
