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
	public function run()
	{
		$this->client = new IRCClient(
			"irc.chat.twitch.tv",
			6667
		);

		$this->client->onMessage = function( $msg ) {
			if($msg === false)
			{
				$this->client->reConnect();
			}
			$this->handle( $msg );
		};

		$this->client->onStart = function() {
			$this->init();
		};

		$this->client->run();
	}

	/**
	 * @param string $msg
	 */
	private function handle(string $msg)
	{
		if(preg_match('/@(.*)\.tmi\.twitch\.tv\sPRIVMSG\s#(.*)\s:(.*)$/', $msg, $matches))
		{
			$this->handleMessage( $matches[1], $matches[3]);
		}
		if(preg_match('/PING (.*)$/', $msg, $matches))
		{
			$this->send('PONG '.$matches[1]);
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
				$data = $this->commands[ $matches[1] ]->handle($this->client, $nickname, $message);
				$this->send($data);
			}
		}
	}

	/**
	 * @param string $message
	 */
	private function send(string $message)
	{
		$this->client->send("PRIVMSG #{$this->channel} :{$message}");
	}

	/**
	 * @return void
	 */
	private function init()
	{
		$init = [
			"PASS {$this->token}",
			"NICK {$this->username}",
			"USER Bot 0 * {$this->username}",
			"JOIN #{$this->channel}"
		];
		foreach($init as $command)
		{
			if(false === ($count = $this->client->send($command)) )
			{
				throw new RuntimeException("Init write error.");
			}
		}
	}
}