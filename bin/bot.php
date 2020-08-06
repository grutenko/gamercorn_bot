<?php
error_reporting(0);
ini_set('display_errors', 0);


use App\Commands\Music;
use App\Twi\Bot;
use App\Ws;
use Dotenv\Dotenv;
use Workerman\Worker;

require __DIR__ . '/../vendor/autoload.php';
Dotenv::createImmutable(__DIR__ . '/..')->load();

include __DIR__. '/../src/functions.php';
$container = new DI\Container;
include __DIR__. '/../src/dependencies.php';

/**
 * BROADCAST CHANNEL
 */
$channel = new Channel\Server(
    getenv('CHANNEL_SERVER_HOST'),
    getenv('CHANNEL_SERVER_PORT')
);

/**
 * CHAT BOT
 */
$bot = new Bot(
	getenv('TWITCH_BOT_USERNAME'),
	getenv('TWITCH_BOT_SECRET'),
	getenv('TWITCH_CHANNEL_NAME')
);

$bot->add(new Music($container));
$bot->enable();

/**
 * WEBSOCKET
 */
$ws = new Ws(getenv('WS_SERVER_URL'));
$ws->enable();

Worker::runAll();