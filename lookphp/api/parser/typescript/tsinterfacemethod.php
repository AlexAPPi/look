<?php

namespace LookPhp\API\Parser\TypeScript;

use LookPhp\API\Parser\TypeScript\TSArgument;

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