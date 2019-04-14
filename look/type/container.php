<?php

namespace Look\Type;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Аналог массива с возможность дополнительных функций
 */
class Container implements ArrayAccess, IteratorAggregate
{
    /** @var ArrayAccess Данные хранящиеся в контейнере */
    protected $containerData = [];
    
    /**
     * Создает новый контейнер данных
     * @param array $data -> Данные
     */
    public function __construct(array $data)
    {
        $this->containerData = $data;
    }
    
    /**
     * Возвращает значение
     * 
     * @param mixed $name -> Назание или индекс элемента
     * @return mixed
     */
    public function getItem($name)
    {
        return $this->meta[$name];
    }
    
    /**
     * Устанавливает значение
     * 
     * @param string $name    -> Название meta тега
     * @param string $content -> Значение для meta тега
     * @return void
     */
    public function setItem($name, $content) : void
    {
        $this->meta[$name] = $content;
    }
    
    /**
     * Добаляет новое значение
     * @param type $content
     * @return void
     */
    public function addItem($name, $content) : void
    {
        $this->meta[$name] = $content;
    }
    
    /**
     * Удаляет элемент
     * 
     * @param string $name -> Название meta тега
     * @return void
     */
    public function unsetItem($name) : void
    {
        if($this->hasItem($name)) {
            unset($this->meta[$name]);
        }
    }
}