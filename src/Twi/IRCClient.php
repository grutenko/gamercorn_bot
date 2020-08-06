<?php


namespace App\Twi;

use Workerman\Worker;
use Closure;
use Workerman\Connection\AsyncTcpConnection;

class IRCClient
{
	/**
	 * @var AsyncTcpConnection
	 */
	protected $irc;

	/**
	 * @var string
	 */
	protected $channel;

	/**
	 * @var Closure
	 */
	public $onMessage,
		   $onStart;

	/**
	 * IRCServer constructor.
	 * @param string $host
	 * @param int $port
	 * @param string $channel
	 */
	public function __construct(string $host, int $port, string $channel)
	{
		$this->url = "tcp://$host:$port";
		$this->channel = $channel;

		$this->onMessage = function() {};
		$this->onStart = function() {};
	}

	/**
	 * @return void
	 */
	public function enable()
	{
		$worker = new Worker();
		$worker->onWorkerStart = function()
		{
			$this->init();
		};
		$worker->onWorkerStop = function()
		{
			$this->privMsg('Отошел.');
			$this->irc->close();
		};
	}

	/**
	 * @return void
	 */
	public function reConnect()
	{
		$this->irc->close();
		$this->init();
	}

	/**
	 * @return void
	 */
	private function init()
	{
		$this->irc = new AsyncTcpConnection($this->url);
		$this->irc->addr = preg_replace('/tcp:\/\//', $this->url, '');
		$this->irc->onConnect = $this->onStart;
		$this->irc->onMessage = function($connection, $data)
		{
			call_user_func($this->onMessage, $data);
		};
		$this->irc->connect();
	}

	/**
	 * @param string $content
	 */
	public function privMsg( string $content )
	{
		$this->send("PRIVMSG #{$this->channel} :{$content}");
	}

	/**
	 * @param string $content
	 * @return bool|null
	 */
	public function send( $content )
	{
		return $this->irc->send($content. "\n");
	}
}