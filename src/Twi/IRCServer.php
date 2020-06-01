<?php


namespace App\Twi;


use Closure;
use RuntimeException;

class IRCServer
{
	/**
	 * @var resource
	 */
	private $socket;

	/**
	 * @var Closure
	 */
	public $onMessage;

	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var int
	 */
	private $port;

	/**
	 * IRCServer constructor.
	 * @param string $host
	 * @param int $port
	 */
	public function __construct(string $host, int $port)
	{
		$this->host = $host;
		$this->port = $port;

		$this->onMessage = function() {};
	}

	/**
	 * @return void
	 */
	private function init()
	{
		$errno = 0;
		$errstr = '';
		$this->socket = fsockopen(
			"tcp://". $this->host,
			$this->port,
			$errno,
			$errstr
		);

		if($errno != 0)
		{
			throw new RuntimeException("SOCK: {$errstr}");
		}
	}

	/**
	 * @param string $content
	 * @return false|int
	 */
	public function send( $content )
	{
		return fwrite($this->socket, $content);
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$this->init();

		$quit = false;
		pcntl_signal(SIGTERM, function() use (&$quit){
			$quit = true;
		});

		while($quit)
		{
			call_user_func($this->onMessage, fgets($this->socket));
		}
	}
}