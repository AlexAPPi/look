<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\DocBlock;
use Look\API\Parser\TypeScript\ITSExportable;

/**
 * Служит для конвертации объекта в TS
 */
abstract class TSExporter implements ITSExportable
{
    /** @var DocBlock|null Блок описания */
    public $desc;
    
    /**
     * Конвертирует объект в TS
     * @param int    $offset     -> Количество отступов от начала строки
     * @param int    $tabSize    -> Количество пробелов в отступе
     * @param string $mainTabStr -> Отступ от начала строки
     * @param string $tabStr     -> Отступ от начала реализации объекта
     */
    protected abstract function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : string;
    
    /**
     * Возвращает список импортируемых зависимостей
     * @return array|null
     */
    public abstract function getImportList() : array;
    
    /**
     * Формирует описание для объекта
     * @param string $mainTabStr -> Отступ от начала строки
     * @param string $tabStr     -> Отступ от начала реализации объекта
     */
    public function buildDesc(string $mainTabStr, string $tabStr = '') : ?string
    {
        if($this->desc)
        {
            $desc = $this->desc->description();
            
            if($desc)
            {
                $tmp  = '';
                // {MAIN_OFFSET}{CLASS_OFFSET} * {LINE}
                foreach(preg_split("/(\r?\n)/", $desc) as $line) {
                    $tmp .= $mainTabStr . $tabStr . " * " . $line . "\n";
                }

                // /**
                //  * {DESC} line 1
                //  * {DESC} line 2
                //  */
                $desc = $mainTabStr . $tabStr . "/**\n" .
                        $tmp .
                        $mainTabStr . $tabStr . " */\n";

                return $desc;
            }
        }
        
        return null;
    }
    
    /** {@inheritdoc} */
    final public function toTS(int $offset = 0, int $tabSize = 4) : string
    {
        $tabStr     = '';
        $mainTabStr = '';
        
        for($i = 0; $i < $tabSize; $i++) {
            $tabStr .= ' ';
        }
        
        for($i = 0; $i < $offset; $i++) {
            $mainTabStr .= $tabStr;
        }
        
        return $this->buildTS($offset, $tabSize, $mainTabStr, $tabStr);
    }
}