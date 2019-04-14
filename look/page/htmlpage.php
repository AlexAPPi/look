<?php

namespace Look\Page;

/**
 * Базовый класс страницы
 */
class HtmlPage
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
}