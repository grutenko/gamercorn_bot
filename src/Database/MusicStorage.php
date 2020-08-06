<?php
namespace App\Database;

class MusicStorage
{
    /**
     * @var array
     */
    public $music = [];

    /**
     * @var integer
     */
    public $current = 0;

    /**
     * @var \Closure
     */
    public $onUpdate;

    /**
     * 
     */
    public function __construct()
    {
        $this->onUpdate = function() {};
    }

    /**
     * Вернет true если музыка с таким названием существует.
     *
     * @param string $music
     * @return bool
     */
    public function exist(string $music): bool
    {
        return in_array($music, array_column($this->music, 'music'));
    }

    /**
     * Добавляет музыку в очередь.
     *
     * @param string $nickname
     * @param string $music
     * @return int
     */
    public function add(string $nickname, string $music)
    {
        $this->music[] = ['nickname' => $nickname, 'music' => $music];
        call_user_func($this->onUpdate, 'add', count($this->music), $this->music);
        return count($this->music);
    }

    /**
     * Удаляет запись из очереди по индексу.
     *
     * @param integer $indx
     * @return void
     */
    public function remove(int $indx)
    {
        if($indx <= count($this->music) - 1)
        {
            array_splice($this->music, $indx, 1);
            call_user_func($this->onUpdate, 'remove', $indx, $this->music);
        }
    }
}