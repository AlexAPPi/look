<?php

namespace LookPhp\API\Parser\TypeScript;

use LookPhp\API\Parser\TypeScript\TSArgumentList;
use LookPhp\API\Parser\TypeScript\TSConstructorArgument;

/**
 * Список аргументов
 */
class TSConstructorArgumentList extends TSArgumentList
{
    /** @var string Тип подставки данных */
    const ItemType = TSConstructorArgument::class;
    
    /**
     * Базовый класс типизироанного массива данных
     * 
     * @param DocBlock|null $desc  -> Блок описания
     * @param TSArgument    $items -> Передаваемые значения
     */
    public function __construct(?DocBlock $desc, TSConstructorArgument ...$items)
    {
        parent::__construct($desc, ...$items);
    }
}