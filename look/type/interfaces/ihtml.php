<?php

namespace Look\Type\Interfaces;

/**
 * Конвертирует объект в HTML файл
 */
interface IHTML
{
    /**
     * Конвертирует объект в HTML
     * @param int $offset  -> Количество отступов от начала строки
     * @param int $tabSize -> Количество пробелов в отступе
     * @return string
     */
    function __toHTML(int $offset = 0, int $tabSize = 4);
}