<?php


namespace App\Twi;

use RuntimeException;

class Bot
{

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $token;

	/**
	 * @var string
	 */
	protected $channel;

	/**
	 * @var IRCServer
	 */
	protected $server;

	/**
	 * @var array
	 */
	protected $commands = [];

	/**
	 * Bot constructor.
	 * @param string $name
	 * @param string $token
	 * @param string $channel
	 */
	public function __construct(string $name, string $token, string $channel)
	{
		$this->username = $name;
		$this->token 	= $token;
		$this->channel 	= $channel;
	}

	/**
	 * @param CommandInterface $command
	 */
	public function add( CommandInterface $command )
	{
		$this->commands[ $command->getName() ] = $command;
	}

	/**
	 * Открывает и инциализирует IRC соединение с twitch
	 * @return void
	 */
	private function init()
	{
		$this->server = new IRCServer( "irc.twitch.tv", 6667 );

		$init = [
			"PASS {$this->token}",
			"NICK {$this->username}",
			"USER {$this->username} 0 * {$this->username}",
			"JOIN #{$this->channel}"
		];
		foreach($init as $command)
		{
			if(false === $this->server->send($command))
			{
				throw new RuntimeException("Init write error.");
			}
		}
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$this->init();

		$this->server->onMessage = function( $command )
		{
			$this->server->send("PRIVMSG #{$this->channel}: Hello world!");
		};
		$this->server->run();
	}
}