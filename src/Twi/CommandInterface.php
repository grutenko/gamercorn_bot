<?php


namespace App\Twi;


interface CommandInterface
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @param IRCClient $client
	 * @param string $nickname
	 * @param string $message
	 * @return mixed
	 */
	public function handle(IRCClient $client, string $nickname, string $message);
}