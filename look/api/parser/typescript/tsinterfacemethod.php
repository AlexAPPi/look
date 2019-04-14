<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\TypeScript\TSArgument;

/**
 * Метод интерфейса
 */
class TSInterfaceMethod
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
}