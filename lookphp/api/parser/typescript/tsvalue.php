<?php

namespace LookPhp\API\Parser\TypeScript;

use LookPhp\API\Parser\Struct\Value;

class TSValue
{
    public $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public function hasValue() : bool
    {
        if($this->value instanceof Value) {
            return true;
        }
        return false;
    }
    
    public function toTS() : string
    {
        if($this->value instanceof Value) {
            return $this->value->value;
        }
        
        return '';
    }
}