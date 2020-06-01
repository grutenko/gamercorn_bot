<?php


namespace App\Twi;


use Closure;
use RuntimeException;

class IRCClient
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
	 * @var Closure
	 */
	public $onStart;

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
		$this->onStart = function() {};
	}

	/**
	 * @return void
	 */
	public function reConnect()
	{
		$this->init();
	}

	/**
	 * @return void
	 */
	private function init()
	{
		$errno = 0;
		$errstr = '';
		$this->socket = fsockopen( $this->host, $this->port, $errno, $errstr );

		if($errno != 0)
		{
			throw new RuntimeException("SOCK: {$errstr}");
		}
		call_user_func($this->onStart);
	}

	/**
	 * @param string $content
	 * @return false|int
	 */
	public function send( $content )
	{
		if(false === ($length = fputs($this->socket, $content. "\r\n")) )
		{
			throw new RuntimeException();
		}

		return $length;
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$this->init();

		$quit = false;

		while(!$quit)
		{
			call_user_func($this->onMessage, fgets($this->socket));
		}
	}
}