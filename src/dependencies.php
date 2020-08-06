<?php
$container->set('music', function() use ($container)
{
    $storage = new \App\Database\MusicStorage;
    $storage->onUpdate = function($type, $indx, $array) use ($container)
    {
        publish_event('music', $type, ['items' => $array]);
    };

    return $storage;
});