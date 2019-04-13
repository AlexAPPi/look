<?php

namespace LookPhp\Event\Interfaces;

/**
 * Данное исключение служит для подмены передаваемых аргументов
 */
interface IEventArgReplace extends IEventTask
{
    /**
     * Возвращает оригинальные аргументы вызываемой функции
     * @return array
     */
    public function getOriginalArgument() : array;
    
    /**
     * Устанавлиает оригинальные аргументы вызываемой функции
     * @param array $arguments
     */
    public function setOriginalArgument(array $arguments) : void;
    
    /**
     * Возвращает оригинальный аргумент по индексу
     * @param int     $index -> индекс аргумента
     * @return mixed
     */
    public function getOriginalArgumentByIndex(int $index);
    
    /**
     * Устаналивает значение оригинального аргумента по индексу
     * @param int     $index -> индекс аргумента
     * @return void
     */
    public function setOriginalArgumentByIndex(int $index, $value) : void;
    
    /**
     * Возвращает измененные аргументы вызываемой функции
     * @return array
     */
    public function getReplaceArgument() : array;
    
    /**
     * Меняет аргументы вызываемой функции
     * @param array $arguments
     */
    public function setReplaceArgument(array $arguments) : void;
    
    /**
     * Возвращает значение аргумента по индексу
     * @param int     $index -> индекс аргумента
     * @return mixed
     */
    public function getReplaceArgumentByIndex(int $index);
    
    /**
     * Устанавливает значение аргумента по индексу
     * @param int     $index -> индекс аргумента
     * @param mixed   $value -> значение
     * @return void
     */
    public function setReplaceArgumentByIndex(int $index, $value) : void;
}