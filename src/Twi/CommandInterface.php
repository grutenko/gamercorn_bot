<?php


namespace App\Twi;


interface CommandInterface extends MessageHandlerInterface
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return void
	 */
	public function init();
}