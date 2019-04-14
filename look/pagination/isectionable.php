<?php

namespace Look\Pagination;

/**
 * Интерфейс реализующий выборку секции
 */
interface ISectionable
{
    /**
     * Возвращает общее количесто элементов
     * @return int
     */
    function count() : int;
    
    /**
     * Задает лимит выборки
     * @param int $value -> Количество элементов выборки
     * @return $this
     */
    function limit(int $value);
    
    /**
     * Задает смещение относительно первого элемента
     * @param int $value -> Количество смещений
     * @return $this
     */
    function offset(int $value);
    
    /**
     * Возвращает результат с учетом лимита и смещения
     */
    function get();
}