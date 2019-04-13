<?php

namespace LookPhp\API\Parser\Struct;

/**
 * Структура аргумента
 */
class Argument
{
    /** @var bool Параметр передается списком */
    public $variadic;
    
    /** @var bool Обязательный аргумент */
    public $required;
    
    /** @var int Позиция аргумента */
    public $position;
    
    /** @var string Назание аргумента */
    public $name;
    
    /** @var TypeStruct|null Тип аргумента */
    public $type;
    
    /** @var mixed Значение по умолчанию */
    public $default;
}