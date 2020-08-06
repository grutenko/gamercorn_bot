<?php

/**
 * @param string $channel
 * @param array $data
 * @return void
 */
function publish_event(string $channel, string $type, array $data)
{
    \Channel\Client::connect(getenv('CHANNEL_SERVER_HOST'), getenv('CHANNEL_SERVER_PORT'));
    \Channel\Client::publish('broadcast', json_encode([
        'channel' => $channel,
        'type' => $type,
        'data' => $data
    ]));
}