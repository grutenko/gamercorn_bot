<?php


namespace App\Twi;

use RuntimeException;
use Workerman\Worker;

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
	 * @var IRCClient
	 */
	protected $client;

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
	 * @return void
	 */
	public function enable()
	{
		$this->client = new IRCClient(
			"irc.chat.twitch.tv",
			6667,
			$this->channel
		);
	
		$this->client->onMessage = [$this, 'handle'];
		$this->client->onStart = [$this, 'init'];
	
		$this->client->enable();
	}

	/**
	 * @param string $msg
	 */
	public function handle(string $msg)
	{
		if(preg_match('/@(.*)\.tmi\.twitch\.tv\sPRIVMSG\s#(.*)\s:(.*)$/', $msg, $matches))
		{
			$this->handleMessage( $matches[1], $matches[3]);
		}
		if(preg_match('/PING (.*)$/', $msg, $matches))
		{
			$this->client->send('PONG '.$matches[1]);
		}
	}

	/**
	 * @param string $nickname
	 * @param string $message
	 */
	private function handleMessage(string $nickname, string $message)
	{
		if( $nickname == $this->username)
		{
			return;
		}

		$matches = [];
		if(preg_match('/^!([^\s]+)\s*(.*)$/', $message, $matches))
		{
			if( isset($this->commands[ $matches[1] ]) )
			{
				$this->commands[ $matches[1] ]->handle($this->client, $nickname, $matches);
			}
		}
	}

	/**
	 * @return void
	 */
	public function init()
	{
		$init = [
			"PASS {$this->token}",
			"NICK {$this->username}",
			"USER Bot 0 * {$this->username}",
			"JOIN #{$this->channel}"
		];
		foreach($init as $command)
		{
			if(false === $this->client->send($command) )
			{
				throw new RuntimeException("Init write error.");
			}
		}

		$this->client->privMsg('Батя в здании.');

		foreach($this->commands as $command)
		{
			$command->init();
		}
	}
}