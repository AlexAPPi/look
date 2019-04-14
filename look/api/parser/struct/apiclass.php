<?php

namespace Look\API\Parser\Struct;

use Look\API\Parser\DocBlock;

/**
 * Структура данных класса
 */
class APIClass
{
    /** @var string DocBlock|string */
    public $comment;
    
    /** @var string */
    public $namespace;
    
    /** @var string */
    public $name;
    
    /** @var array[MethodStruct] */
    public $methods;
}