<?php


namespace App\TriggerHandlers;


use App\Twi\IRCClient;
use App\Twi\TriggerHandlerInterface;

class CrayfishHandler implements TriggerHandlerInterface
{

	/**
	 * @inheritDoc
	 */
	public function handle(IRCClient $client, string $nickname, string $message)
	{
		$client->privMsg("{$nickname}, еще раз и я вызываю полицию.");
	}

	/**
	 * @inheritDoc
	 */
	public function getRgx(): string
	{
		return '/(Л|л)(е|ё)ня рак/';
	}
}