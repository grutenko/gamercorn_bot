<?php


namespace App\Commands;


use App\Twi\CommandInterface;
use App\Twi\IRCClient;

class Cuts implements CommandInterface
{

	/**
	 * @inheritDoc
	 */
	public function getName(): string
	{
		return 'кусь';
	}

	/**
	 * @inheritDoc
	 */
	public function handle(IRCClient $client, string $nickname, string $message)
	{
		return 'Какой то хер сделал какому то хру кусь.';
	}
}