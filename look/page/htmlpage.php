<?php

namespace Look\Page;

/**
 * Базовый класс страницы
 */
class HtmlPage extends HTMLWrap
{
    /** @var \Look\Page\Head */
    public $head;
    public $controller;
    public $view;
    
    public function __construct()
    {
        $this->head = new Head();
    }
    
    /**
     * Возвращает значение meta тега
     * 
     * @param string $name -> Название meta тега
     * @return string|null
     */
    public function getMeta(string $name) : ?string
    {
        return $this->head->meta->get($name);
    }
    
    /**
     * Устанавливает meta тег для страницы
     * 
     * @param string $name    -> Название meta тега
     * @param string $content -> Значение для meta тега
     * @return void
     */
    public function setMeta(string $name, string $content) : void
    {
        $this->head->meta->set($name, $content);
    }
    
    /**
     * Проверяет задан ли такой meta тег
     * 
     * @param string $name -> Название meta тега
     * @return bool
     */
    public function hasMeta(string $name) : bool
    {
        return $this->head->meta->has($name);
    }
    
    /**
     * Возвращает зеркало для страницы или тэг
     * @param boolean $tag -> Вернуть html тэг
     * @return string|null
     */
    public function getCanonical(bool $tag = false) : ?string
    {
        return $this->head->navigation->getCanonical($tag);
    }    

    /**
     * Устанавливает зеркало для страницы
     * @param string $url -> URL зеркальной страницы
     * @return void
     */
    public function setCanonical(string $url) : void
    {
        $this->head->navigation->setCanonical($url);
    }
    
    /**
     * Проверяет, является ли данная страница каноничной
     * @param string $url -> URL зеркальной страницы
     * @return bool
     */
    public function hasCanonical() : bool
    {
        return $this->head->navigation->hasCanonical();
    }

    /**
     * Возвращает следующую страницу пагинации
     * @param boolean $tag -> Вернуть html тэг
     * @return string|null
     */
    public function getPrevPagination(bool $tag = false) : ?string
    {
        return $this->head->navigation->getPrevPagination($tag);
    }
    
    /**
     * Устанавливает предыдущую страницу пагинации
     * @param string $url -> URL страницы
     * @return void
     */
    public function setPrevPagination(string $url) : void
    {
        $this->head->navigation->setPrevPagination($url);
    }
    
    /**
     * Проверяет существует ли предыдущая страница пагинации
     * @return void
     */
    public function hasPrevPagination() : bool
    {
        return $this->head->navigation->hasPrevPagination();
    }
    
    /**
     * Возвращает следующую страницу пагинации
     * 
     * @param boolean $tag -> Вернуть html тэг
     * @return string
     */
    public function getNextPagination(bool $tag = false) : ?string
    {
        return $this->head->navigation->getNextPagination($tag);
    }
    
    /**
     * Устанавливает следующую страницу пагинации
     * 
     * @param string $url -> URL страницы
     * @return void
     */
    public function setNextPagination(string $url) : void
    {
        $this->head->navigation->setNextPagination($url);
    }
    
    /**
     * Проверяет существует ли следующая страница пагинации
     * @return void
     */
    public function hasNextPagination() : bool
    {
        return $this->head->navigation->hasNextPagination();
    }
    
    /**
     * Добавляет робота аналогичного robots
     * @param string $name
     */
    public function addRobot(string $name) : void
    {
        $this->head->robots->add($name);
    }
    
    /**
     * Добавляет флаг роботу
     * @param string $name  -> Название бота (* - для всех ботов)
     * @param string $index -> Индекс для бота (AccessIndex, AccessFollow, NoArchive)
     * @param bool   $flag  -> Активность индекса
     */
    public function setRobotValue(string $name, string $index, bool $flag) : void
    {
        $this->head->robots->set($name, $index, $flag);
    }
    
    /**
     * Возвращает флаг робота
     * @param string $name  -> Название бота
     * @param string $index -> Индекс для бота (AccessIndex, AccessFollow, NoArchive)
     * @return bool|null
     */
    public function getRobotValue(string $name, string $index) : ?bool
    {
        return $this->head->robots->get($name, $index);
    }
    
    /**
     * Показывать ссылку на сохраненную копию на странице результатов поиска
     * @param bool   $flag -> Флаг
     * @param string $name -> Название робота
     * @return void
     */
    public function robotNoArchive(bool $flag = true, $name = 'robots') : void
    {
        $this->head->robots->noArchive($flag, $name);
    }
    
    /**
     * Показывать ссылку на сохраненную копию на странице результатов поиска
     * @param string $name -> Название робота
     * @return bool|null
     */
    public function robotNoArchiveValue($name = 'robots') : ?bool
    {
        return $this->head->robots->noArchiveValue($name);
    }
    
    /**
     * Индексировать текст на странице
     * @param bool   $flag -> Флаг
     * @param string $name -> Название робота
     * @return void
     */
    public function accessIndex($flag = true, $name = 'robots') : void
    {
        $this->head->robots->accessIndex($flag, $name);
    }
    
    /**
     * Индексировать текст на странице
     * @param string $name -> Название робота
     * @return bool|null
     */
    public function robotAccessIndexValue($name = 'robots') : ?bool
    {
        return $this->head->robots->accessIndexValue($name);
    }
    
    /**
     * Индексировать ссылки на странице
     * @param bool   $flag -> Флаг
     * @param string $name -> Название робота
     * @return void
     */
    public function robotAccessFollow($flag = true, $name = 'robots') : void
    {
        $this->head->robots->accessFollow($flag, $name);
    }
    
    /**
     * Индексировать ссылки на странице
     * @param string $name -> Название робота
     * @return bool|null
     */
    public function robotAccessFollowValue($name = 'robots') : ?bool
    {
        return $this->head->robots->accessFollowValue($name);
    }
    
    /** {@inheritdoc} */
    public function buildHTML(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : ?string
    {
        $html  = "$mainTabStr<html>";
        $html .= $this->head->__toHTML($offset + 1, $tabSize);
        $html .= "$mainTabStr</html>";
        
        return $html;
    }
}