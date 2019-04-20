<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\TypeScript\TSArgument;

/**
 * Метод интерфейса
 */
class TSInterfaceMethod extends TSExporter
{    
    public $isStatic;
    public $name;
    public $arguments;
    
    /**
     * Метод
     * @param bool          $static   -> Метод статичный
     * @param string        $name     -> Название
     * @param ...TSArgument $argument -> Аргументы
     */
    public function __construct(bool $static, string $name, TSArgument ... $argument)
    {
        $this->isStatic  = $static;
        $this->name      = $name;
        $this->arguments = $argument;
    }
    
    /** {@inheritdoc} */
    public function getImportList() : array
    {
        $result = [];
        
        if($this->arguments && count($this->arguments) > 0) {
            foreach($this->arguments as $arg) {
                $result = array_merge($result, $arg->getImportList());
            }
        }
        
        return $result;
    }
}