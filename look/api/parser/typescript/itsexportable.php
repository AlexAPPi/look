<?php

namespace Look\API\Parser\TypeScript;

/**
 * Данный объект может конвертироваться в TypeScript
 */
interface ITSExportable
{
    /**
     * Конвертирует объект в TS
     * @param int $offset  -> Количество отступов от начала строки
     * @param int $tabSize -> Количество пробелов в отступе
     * @return string
     */
    function toTS(int $offset = 0, int $tabSize = 4) : string;
}