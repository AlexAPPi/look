<?php

namespace Look\API\Parser\DocBlock;

/**
 * Блок документации параметра
 */
class ParamDocBlock
{
    /** @var string Тип */
    public $type;
    
    /** @var string Назание */
    public $name;
    
    /** @var string Описание */
    public $desc;
    
    /**
     * Блок документации параметра
     * @param string $type -> Тип
     * @param string $name -> Название
     * @param string $desc -> Описание
     */
    public function __construct(string $type, string $name, string $desc)
    {
        $this->type = $type;
        $this->name = $name;
        $this->desc = $desc;
    }
}