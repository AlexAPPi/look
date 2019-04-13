<?php

namespace LookPhp\Event;

/**
 * Позволяет классу использовать систему событий
 */
trait Eventable
{
    /**
     * Событие формирующееся по вызову
     * 
     * @param string   $eventName -> Назание события
     * @param string   $func      -> Назание метода
     * @param array    $arguments -> Сслки на аргументы
     * @param mixed    $result    -> Промежуточные результаты
     * @param callable $handler   -> Обработчик промежуточного результата события
     */
    public static function __event(string $eventName, string $func, array $arguments, callable $handler = null)
    {
        return (new Event($eventName, static::class, $func))
                ->exec($handler, $arguments);
    }
    
    /**
     * Событие формирующееся при вызову метода
     * 
     * Обработчик данного события должен принимать такие же аргументы как и вызываемая функция.
     * 
     * @param string   $func      -> Назание метода
     * @param array    $arguments -> Сслки на аргументы
     * @param mixed    $result    -> Промежуточные результаты
     * @param callable $handler   -> Обработчик промежуточного результата события
     */
    public static function __eventCall(string $func, array &$arguments, &$result, callable $handler = null)
    {
        return (new Event('call', static::class, $func))
                ->execRef($handler, $arguments, $result);
    }
    
    /**
     * Событие формирующееся при возвращении результата методом
     * 
     * Обработчик данного события должен принимать 2 параметра:
     * 
     * 1 - массив аргументов,
     * 2 - результат
     * 
     * @param string  $func      -> Назване метода
     * @param array   $arguments -> Аргументы
     * @param mixed   $result    -> Результат полученный стандартной функцией
     * @param callable $handler  -> Обработчик промежуточного результата события
     * @return mixed
     */
    public static function __eventReturn(string $func, array &$arguments, &$result, callable $handler = null)
    {
        return (new Event('return', static::class, $func))
                ->execRef($handler, $arguments, $result);
    }
}