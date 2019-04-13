<?php

namespace LookPhp\API\Parser\Struct;

use LookPhp\API\Parser\DocBlock;

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