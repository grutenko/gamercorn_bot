<?php


namespace App\Twi;


interface TriggerHandlerInterface extends MessageHandlerInterface
{
	/**
	 * Возвращает паттерн для сообщения чата по которому будет запускаться обработчик.
	 * @return string
	 */
	public function getRgx(): string;
}