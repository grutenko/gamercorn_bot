<?php

use App\Commands\Cuts;
use App\Twi\Bot;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
Dotenv::createImmutable(__DIR__ . '/..')->load();

$bot = new Bot(
	getenv('TWITCH_BOT_USERNAME'),
	getenv('TWITCH_BOT_SECRET'),
	getenv('TWITCH_CHANNEL_NAME')
);

$bot->add(new Cuts());

$bot->run();

