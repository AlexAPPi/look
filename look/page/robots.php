<?php

namespace Look\Page;

use Look\Type\HTMLWrap;
use Look\Exceptions\SystemLogicException;

/**
 * Класс реализующий работу индексации для поисковых роботов
 */
class Robots extends HTMLWrap
{
    /** @var string Индексирует текст и ссылки на странице */
    const AccessAll    = 'all';
    /** @var string Индексирует текст на странице */
    const AccessIndex  = 'index';
    /** @var string Индексирует ссылки на странице */
    const AccessFollow = 'follow';
    
    /** @var string Не индексировать текст страницы */
    const NoIndex      = 'noindex';
    /** @var string Не переходить по ссылкам на странице */
    const NoFollow     = 'nofollow';
    /** @var string Не показывать ссылку на сохраненную копию на странице результатов поиска */
    const NoArchive    = 'noarchive';
    /** @var string Запрещено индексировать текст и переходить по ссылкам на странице */
    const NoAccess     = 'none';
    
    /**
     * @var array Заголовки meta robots
     */
    protected $robots = [];

    /**
     * Класс реализующий работу индексации для поисковых роботов
     */
    public function __construct()
    {
        
    }
    
    /**
     * Добавляет робота аналогичного robots
     * @param string $name
     * @return void
     */
    public function add(string $name) : void
    {
        if($name == '*') {
            throw new SystemLogicException('robot can\'t have name *');
        }
        
        $this->robots[$name] = [
            static::AccessIndex  => true,
            static::AccessFollow => true,
            static::NoArchive    => false
        ];
    }
    
    /**
     * Добавляет флаг роботу
     * @param string $name  -> Название бота (* - для всех ботов)
     * @param string $index -> Индекс для бота (AccessIndex, AccessFollow, NoArchive)
     * @param bool   $flag  -> Активность индекса
     * @return void
     */
    public function set(string $name, string $index, bool $flag) : void
    {
        if($name == '*') {
            $names = array_keys($this->robots);
            foreach($names as $name) {
                $this->set($name, $index, $flag);
            }
            return;
        }
        
        if(!isset($this->robots[$name])) {
            $this->add($name);
        }
        
        $this->robots[$name][$index] = $flag;
    }
    
    /**
     * Возвращает флаг робота
     * @param string $name  -> Название бота
     * @param string $index -> Индекс для бота (AccessIndex, AccessFollow, NoArchive)
     * @return bool|null
     */
    public function get(string $name, string $index) : ?bool
    {
        if(!isset($this->robots[$name])
        || !isset($this->robots[$name][$index])) {
            return null;
        }
        
        return $this->robots[$name][$index];
    }
    
    /**
     * Показывать ссылку на сохраненную копию на странице результатов поиска
     * @param bool   $flag -> Флаг
     * @param string $name -> Название робота
     * @return void
     */
    public function noArchive(bool $flag = true, $name = 'robots') : void
    {
        $this->set($name, static::NoArchive, $flag);
    }
    
    /**
     * Показывать ссылку на сохраненную копию на странице результатов поиска
     * @param string $name -> Название робота
     * @return bool|null
     */
    public function noArchiveValue($name = 'robots') : ?bool
    {
        return $this->get($name, static::NoArchive);
    }
    
    /**
     * Индексировать текст на странице
     * @param bool   $flag -> Флаг
     * @param string $name -> Название робота
     * @return void
     */
    public function accessIndex($flag = true, $name = 'robots') : void
    {
        $this->set($name, static::AccessIndex, $flag);
    }
    
    /**
     * Индексировать текст на странице
     * @param string $name -> Название робота
     * @return bool|null
     */
    public function accessIndexValue($name = 'robots') : ?bool
    {
        return $this->get($name, static::AccessIndex);
    }
    
    /**
     * Индексировать ссылки на странице
     * @param bool   $flag -> Флаг
     * @param string $name -> Название робота
     * @return void
     */
    public function accessFollow($flag = true, $name = 'robots') : void
    {
        $this->set($name, static::AccessFollow, $flag);
    }
    
    /**
     * Индексировать ссылки на странице
     * @param string $name -> Название робота
     * @return bool|null
     */
    public function accessFollowValue($name = 'robots') : ?bool
    {
        return $this->get($name, static::AccessFollow);
    }
    
    /** {@inheritdoc} */
    protected function buildHTML(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : ?string
    {
        $html = '';        
        foreach($this->robots as $name => $falgs) {

            if($falgs[static::AccessIndex] === true && $falgs[static::AccessFollow] === true)
            { $html = $mainTabStr . '<meta name="'.$name.'" content="index, follow"/>'.PHP_EOL; }
            else if($falgs[static::AccessIndex] === true && $falgs[static::AccessFollow] === false)
            { $html = $mainTabStr . '<meta name="'.$name.'" content="index, nofollow"/>'.PHP_EOL; }
            else if($falgs[static::AccessIndex] === false && $falgs[static::AccessFollow] === true)
            { $html = $mainTabStr . '<meta name="'.$name.'" content="noindex, follow"/>'.PHP_EOL; }
            else
            { $html = $mainTabStr . '<meta name="'.$name.'" content="noindex, nofollow"/>'.PHP_EOL; }
            
            // Не показывать ссылку на сохраненную копию на странице результатов поиска
            if($falgs[static::NoArchive] === true)
            { $html .= $mainTabStr . '<meta name="'.$name.'" content="noarchive"/>'.PHP_EOL; }
        }
        
        return $html;
    }
}