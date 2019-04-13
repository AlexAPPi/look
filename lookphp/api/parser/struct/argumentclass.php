<?php

namespace LookPhp\API\Parser\Struct;

use LookPhp\API\Parser\DocBlock;

/**
 * Структура данных класса аргумента
 */
class ArgumentClass
{
    /** @var string */
    public $namespace;
    
    /** @var string */
    public $name;
    
    /** @var string DocBlock|string */
    public $comment;
    
    /** @var MethodStruct */
    public $constructor;
}