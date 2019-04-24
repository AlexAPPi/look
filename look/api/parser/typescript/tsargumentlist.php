<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\DocBlock;
use Look\API\Parser\TypeScript\TSArgument;
use Look\API\Parser\DocBlock\ParamDocBlock;

use Look\Type\ObjectArray;

/**
 * Список аргументов
 */
class TSArgumentList extends ObjectArray
{
    /** Тип объекта */
    const EvalItemType = TSArgument::class;
    
    protected $desc;

    /**
     * Базовый класс типизироанного массива данных
     * 
     * @param DocBlock|null $desc  -> Блок описания
     * @param TSArgument    $items -> Передаваемые значения
     */
    public function __construct(?DocBlock $desc, TSArgument ...$items)
    {
        $this->desc = $desc;
        parent::__construct(...$items);
    }
    
    /** {@inheritdoc} */
    public function getImportList(): array
    {
        $result = [];
        
        if($this->m_array && count($this->m_array) > 0) {
            foreach($this->m_array as $arg) {
                $result = array_merge($result, $arg->getImportList());
            }
        }
        
        return $result;
    }
    
    /**
     * Формирует документацию для аргументов
     * @param string $mainTabStr -> Отступ от начала строки
     * @param string $tabStr     -> Отступ от начала реализации объекта
     * @return string|null
     */
    public function buildDesc(string $mainTabStr, string $tabStr = '') : ?string
    {
        if($this->desc
        && $this->m_array
        && count($this->m_array) > 0) {

            $sortList = [];
            foreach($this->m_array as $arg) {
                $sortList[$arg->position] = $arg->name;
            }
            ksort($sortList);

            $paramDesc = $this->desc->param;
            
            if($paramDesc) {
                $result = "";
                foreach($sortList as $argName) {
                    if(isset($paramDesc[$argName])) {
                        $extractDoc = $paramDesc[$argName];
                        if($extractDoc instanceof ParamDocBlock) {
                            // * {NAME} {DESC}
                            $result .= $mainTabStr . $tabStr . " * @param $extractDoc->name $extractDoc->desc\n";
                        }
                    }
                }
                return $result;
            }
        }
        
        return null;
    }
    
    /**
     * Преобразует объект в TypeScript
     * @return string|null
     */
    public function toTS() : ?string
    {
        if($this->m_array && count($this->m_array) > 0) {
            
            $sortList = [];
            foreach($this->m_array as $arg) {
                $sortList[$arg->position] = $arg;
            }
            ksort($sortList);
            $result = "";
            foreach($sortList as $argument) {
                $result .= ', ' . $argument->toTS(0, 0);
            }
            
            return substr($result, 2);
        }
        
        return null;
    }
}