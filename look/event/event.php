<?php

namespace Look\Event;

use Look\Event\EventResult;
use Look\Event\EventSelector;
use Look\Event\EventManager;

/**
 * Базовое представление события
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class Event
{
    /** @var \Look\Event\EventSelector Селектор  */
    protected $selector;

    /**
     * Формирует новое событие
     * 
     * @param string $name
     * @param string|null $class
     * @param string|null $func
     */
    public function __construct(string $name, ?string $class = null, ?string $func = null)
    {
        $this->selector = new EventSelector($name, $class, $func);
    }
        
    /**
     * Возвращает селектор данного события
     * Он формирутеся из данных класса и функции
     * @return \Look\Event\EventSelector
     */
    public function getSelector() : EventSelector
    {
        return $this->selector;
    }
        
    /**
     * Запускает процесс обработки события
     * 
     * @param callable|null $handler  -> Обработчик
     * @param array         $argument -> Аргументы запуска обернутые в массив
     * @param mixed         $result   -> Результаты функции
     * @return type
     */
    public function execRef(?callable $handler = null, array &$argument, &$result)
    {
        return EventManager::exec($this, $argument, $result, $handler);
    }
    
    /**
     * Запускает процесс обработки события
     * 
     * @param callable|null $handler  -> Обработчик
     * @param mixed         $argument -> Аргументы запуска обернутые в массив
     * @return mixed
     */
    public function exec(?callable $handler = null, ...$argument)
    {
        $result = null;
        return EventManager::exec($this, $argument, $result, $handler);
    }
}