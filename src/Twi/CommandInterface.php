<?php


namespace App\Twi;


interface CommandInterface
{
	/**
	 * @return string
	 */
	public function getName(): string;
}