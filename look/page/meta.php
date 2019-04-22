<?php

namespace Look\Page;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Класс для работы с meta данными страницы
 */
class Meta extends HTMLWrap implements ArrayAccess, IteratorAggregate
{
    /**
     * Мета данные
     * @var array
     */
    protected $meta = [];

    /**
     * Возвращает значение meta тега
     * 
     * @param string $name -> Название meta тега
     * @return string|null
     */
    public function get(string $name) : ?string
    {
        return $this->meta[$name];
    }
    
    /**
     * Устанавливает meta тег для страницы
     * 
     * @param string $name    -> Название meta тега
     * @param string $content -> Значение для meta тега
     * @return void
     */
    public function set(string $name, string $content) : void
    {
        $this->meta[$name] = $content;
    }
        
    /**
     * Удаляет мета тег
     * 
     * @param string $name -> Название meta тега
     * @return void
     */
    public function unset(string $name) : void
    {
        if($this->has($name)) {
            unset($this->meta[$name]);
        }
    }
    
    /**
     * Проверяет задан ли такой meta тег
     * 
     * @param string $name    -> Название meta тега
     * @return bool
     */
    public function has(string $name) : bool
    {
        return isset($this->meta[$name]);
    }
    
    /**
     * Проверяет существует ли мета данные
     * @param string $name -> Название мета данных 
     * @return bool
     */
    public function offsetExists($name) : bool
    {
        return $this->has($name);
    }
    
    /**
     * Возвращает мета данные
     * @param string $name -> Название метаданных
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Устанавливает meta тег для страницы
     * 
     * @param string $name    -> Название meta тега
     * @param string $content -> Значение для meta тега
     */
    public function offsetSet($name, $content) : void
    {
        $this->set($name, $content);
    }
    
    /**
     * Удаляет мета тег
     * 
     * @param string $name -> Название meta тега
     * @return bool
     */
    public function offsetUnset($name) : void
    {
        $this->unset($name);
    }
    
    /** @see IteratorAggregate */
    public function getIterator()
    {
        return new ArrayIterator($this->meta);
    }
    
    /**
     * Конструирует meta тег
     * <b>Пустой тег не выводится</b>
     * 
     * @param string $name    -> Название meta тега
     * @param string $content -> Значение для meta тега
     * @return string
     */
    public function buildMetaTag($name, $content = null)
    {
        $content = is_null($content) ? $this->meta[$name] : $content;
        
        if(is_array($content)) {
            $content = implode(',', $content);
        }
        
        // Пропускаем null или пустую строку
        if(!isset($content) || strlen((string)$content) == 0)
            return '';
        
        $tmp1 = (string)$name;
        $tmp2 = (string)$content;
        
        if(strlen($tmp1) == 0 || strlen($tmp2) == 0)
            return '';
        
        return '<meta name="' . $tmp1 . '" content="' . $tmp2 . '"/>' . PHP_EOL;
    }
    
    /**
     * Конструирует meta данные для страницы
     */
    public function getHTML()
    {
        $html = '';
        foreach($this->meta as $name => $content) {
            $html .= $this->buildMetaTag($name, $content);
        }
        return $html;
    }
    
    /** {@inheritdoc} */
    public function buildHTML(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : ?string
    {
        $html = '';
        foreach($this->meta as $name => $content) {
            $html .= $mainTabStr . $this->buildMetaTag($name, $content);
        }
        return $html;
    }
}