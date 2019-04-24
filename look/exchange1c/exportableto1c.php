<?php

namespace Look\Exchange1C;

use SimpleXMLElement;

/**
 * Интерфейс преобразования объекта в CommerceML2 формат
 */
interface ExportableTo1C
{
    /**
     * Преобразует объект в CommerceML2 формат
     * @param SimpleXMLElement $parentElement -> Родительский блок
     * @param string           $versionCode   -> Код версии файла
     */
    function toCommerceML2(SimpleXMLElement &$parentElement, string $versionCode) : SimpleXMLElement;
}
