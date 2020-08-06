<?php


namespace App\Twi;

/**
 * Базовый интерфейс для всех обработчиков сообщений чата
 * @package App\Twi
 */
interface MessageHandlerInterface
{
	/**
	 * Обрабатывает сообщение
	 *
	 * @param IRCClient $client
	 * @param string $nickname
	 * @param array $matches
	 * @return void
	 */
	public function handle(IRCClient $client, string $nickname, array $matches);
}