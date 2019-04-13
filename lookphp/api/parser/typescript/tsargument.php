<?php

namespace LookPhp\API\Parser\TypeScript;

use LookPhp\API\Parser\TypeScript\TSType;
use LookPhp\API\Parser\TypeScript\TSValue;
use LookPhp\API\Parser\TypeScript\TSExporter;

/**
 * Объект аргумента
 */
class TSArgument extends TSExporter
{
    public $name;
    public $type;
    public $default;
    public $required;
    public $variadic;
    public $position;
    public $desc;
    
    /**
     * Аргумент
     * 
     * @param string $name
     * @param mixed  $type
     * @param mixed  $default
     * @param bool   $required
     * @param bool   $variadic
     * @param int    $position
     */
    public function __construct(string $name, TSType $type, TSValue $default, bool $required, bool $variadic, int $position)
    {
        $this->name     = $name;
        $this->type     = $type;
        $this->default  = $default;
        $this->required = $required;
        $this->variadic = $variadic;
        $this->position = $position;
    }
    
    /** {@inheritdoc} */
    public function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : string
    {
        $str  = $mainTabStr . $tabStr . "$this->name";
        $type = $this->type->toTS(0, 0);
        
        if($this->required) {
            $str .= ": " . $type;
        } else {
            
            $defValue = $this->default->toTS(0, 0);
            
            // Если значение по умолчанию доступно
            if($defValue != '') $str .= ": $type = " . $defValue;
            else                $str .= "?: $type";
        }
        
        return $str;
    }
}