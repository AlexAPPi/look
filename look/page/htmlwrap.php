<?php

namespace Look\Page;

/**
 * Представляет собой обертку преобразования в HTML документ
 */
abstract class HTMLWrap implements IHTML
{
    /**
     * Преобразует объект в строку
     * @return string
     */
    public function __toString() : string
    {
        return $this->__toHTML();
    }
    
    /**
     * Конвертирует объект в HTML
     * @param int    $offset     -> Количество отступов от начала строки
     * @param int    $tabSize    -> Количество пробелов в отступе
     * @param string $mainTabStr -> Отступ от начала строки
     * @param string $tabStr     -> Единый отступ
     */
    protected abstract function buildHTML(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : ?string;
    
    /** {@inheritdoc} */
    public function __toHTML(int $offset = 0, int $tabSize = 4) : string
    {
        $tabStr     = '';
        $mainTabStr = '';
        
        for($i = 0; $i < $tabSize; $i++) {
            $tabStr .= ' ';
        }
        
        for($i = 0; $i < $offset; $i++) {
            $mainTabStr .= $tabStr;
        }
        
        return $this->buildHTML($offset, $tabSize, $mainTabStr, $tabStr);
    }
}