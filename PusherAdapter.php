<?php namespace Hht\Pusher;

use Hht\MiPush\PusherInterface;
use Hht\Support\Contracts\Message as Message;
use Hht\Support\Contracts\Pusher as PusherContract;

class PusherAdapter implements PusherContract
{
	/**
     * The pusher instance.
     *
     * @var \Hht\MiPush\PusherInterface
     */
    protected $driver;

	/**
     * Create a new pusher adapter instance.
     *
     * @param  \Hht\MiPush\PusherInterface  $driver
     * @return void
     */
	public function __construct(PusherInterface $driver)
    {
        $this->driver = $driver;
    }

	/**
     * Get a pusher instance.
     *
     * @return \Hht\MiPush\PusherInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }
	
	/**
     * Send a message instance.
     *
     * @param  \Hht\Support\Contracts\Message  $message
     * @return \Hht\Support\Contracts\Result
     */
	public function send(Message $message)
	{
		return $this->driver->send($message);
	}
	
	/**
     * Pass dynamic methods call onto Pusher.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->driver, $method], $parameters);
    }
}