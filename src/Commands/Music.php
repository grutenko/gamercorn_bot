<?php

namespace App\Commands;

use App\Twi\CommandInterface;
use App\Twi\IRCClient;
use DI\Container;
use App\Database\MusicStorage;

class Music implements CommandInterface
{
    /**
     * @var MusicStorage
     */
    protected $music;

    public function __construct(Container $container)
    {
        $this->music = $container->get('music');
    }

    public function getName(): string
    {
        return 'music';
    }

    public function init()
    {
        $this->addBroadcastListener();
    }

    public function handle(IRCClient $client, string $nickname, array $matches)
    {
        list(, , $params) = $matches;

        if($params == null || strlen($params) == 0)
        {
            $client->privMsg("Правила: Одна команда - один трек. Треш скипаю.");
        }
        elseif($this->music->exist($params))
        {
            $client->privMsg('music: Уже в очереди!');
        }
        elseif(trim($params) == 'list')
        {
            $this->showList($client);
        }
        else
        {
            if(!preg_match('/.+-.+/', $params))
            {
                $client->privMsg('music: <Автор> - <Название>');
            }
            else
            {
                $client->privMsg('music: В очереди #'. $this->music->add($nickname, $params));
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param IRCClient $client
     * @return void
     */
    private function showList(IRCClient $client)
    {
        foreach($this->music->music as $indx => $music)
        {
            $client->privMsg('#'. ($indx+1). ' '. $music['music']);
        }
    }

    /**
     * @return void
     */
    private function addBroadcastListener()
    {
        \Channel\Client::connect(getenv('CHANNEL_SERVER_HOST'), getenv('CHANNEL_SERVER_PORT'));
        \Channel\Client::on('broadcast', function($data)
        {
            $arData = json_decode($data, true);
            if($arData['channel'] == 'connect')
            {
                publish_event('music', 'add', [
                    'for' => $arData['data']['id'],
                    'items' => $this->music->music
                ]);
            }
            elseif($arData['channel'] == 'manage')
            {
                $this->music->remove($arData['data']['indx']);
            }
        });
    }
}

