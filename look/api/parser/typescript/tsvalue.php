<?php

namespace Look\API\Parser\TypeScript;

use Look\Type\Interfaces\IType;
use Look\API\Parser\Struct\Value;

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
    
    /** {@inheritdoc} */
    public function getImportList() : array
    {
        $result = [];
        
        return $result;
    }
    
    /** {@inheritdoc} */
    public function toTS() : string
    {
        $value = $this->value;
        
        if($value instanceof Value) {
            
            if($value->type == IType::TString) {
                return "\"$value->value\"";
            }
            
            return "$value->value";
        }
        
        return "$value";
    }
    
    public function __toString()
    {
        return $this->toTS();
    }
}