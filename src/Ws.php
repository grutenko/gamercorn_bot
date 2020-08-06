<?php

namespace App;

use Workerman\Worker;
use Workerman\Timer;

class Ws
{
    /**
     * @var string
     */
    protected $address;

    /**
     * @var array
     */
    protected $clients = [];

    /**
     * @param string $address
     */
    public function __construct(string $address)
    {
        $this->address = $address;
    }

    /**
     * @param array $data
     * @return void
     */
    private function forwardData(array $data)
    {
        $clients = array_filter(
            $this->clients,
            function($item) use($data)
            {
                return $data['channel'] == $item['channel'];
            }
        );
        $conns = array_map(
            function($item)
            {
                return $item['connection'];
            },
            $clients
        );

        if(count($conns) > 0)
        {
            foreach($conns as $conn) $conn->send(json_encode($data));
        }
    }

    /**
     * @return void
     */
    public function enable()
    {
        $this->worker = new Worker($this->address);
        $this->worker->onWebSocketConnect = function($connection)
        {
            $this->clients[ $connection->id ] = [
                'connection' => $connection,
                'channel'    => @$_GET['channel'] ?: 'music'
            ];
            publish_event('connect', 'add', ['id' => $connection->id]);
        };
        $this->worker->onMessage = function($connection, $data)
        {
            $arData = json_decode($data, true);
            publish_event($arData['channel'], $arData['type'], $arData['data']);
        };
        $this->worker->onWorkerStart = function()
        {
            $this->addPingTimer();
            $this->addBroadcastListener();
        };
        $this->worker->onClose = function($connection)
        {
            unset($this->clients[$connection->id]);
        };
    }

    /**
     * @return void
     */
    private function addPingTimer()
    {
        Timer::add(10, function()
        {
            foreach($this->worker->connections as $connection) {
                $connection->send(pack('H*', '890400000000'), true);
            }
        });
    }

    /**
     * @return void
     */
    private function addBroadcastListener()
    {
        \Channel\Client::connect(getenv('CHANNEL_SERVER_HOST'), getenv('CHANNEL_SERVER_PORT'));
        \Channel\Client::on('broadcast', function($data)
        {
            $this->forwardData(json_decode($data, true));
        });
    }
}