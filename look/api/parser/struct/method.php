<?php

namespace Look\API\Parser\Struct;

use Look\API\Parser\DocBlock;

/**
 * Структура метода
 */
class Method
{
    /** @var string Назание метода */
    public $name;
    
    /** @var string DocBlock|string */
    public $comment;
    
    /** @var array[ArgumentStruct] Список аргументов */
    public $arguments;
}