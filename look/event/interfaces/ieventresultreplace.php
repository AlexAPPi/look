<?php

namespace Look\Event\Interfaces;

/**
 * Данное исключение служит для подмены возвращаемого результата
 */
interface IEventResultReplace extends IEventTask
{
    /**
     * Возвращает оригинальный результат
     * @return mixed
     */
    public function getOriginalResult();
    
    /**
     * Устанавлиает оригинальный результат
     * @param mixed $result
     */
    public function setOriginalResult($result) : void;
    
    /**
     * Возвращает измененный результат
     * @return mixed
     */
    public function getReplaceResult();
    
    /**
     * Изменяет результат
     * @return mixed
     */
    public function setReplaceResult($result) : void;
}