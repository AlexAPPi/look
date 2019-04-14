<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\Struct\Type;
use Look\API\Parser\TypeScript\TSExporter;
use Look\API\Parser\Exceptions\ParserException;

class TSType extends TSExporter
{
    public $type;
    
    public function __construct($type)
    {
        $this->type = $type;
    }
    
    /** {@inheritdoc} */
    protected function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : string
    {
        $type = $this->type;
        if($type instanceof Type)
        {
            if($type->isScalar)
            {
                switch($type->class)
                {
                    case 'object': return 'Object';
                    case 'array':  return 'Array<any>';
                    case 'int':    return 'number';
                    case 'float':  return 'number';
                    case 'string': return 'string';
                    case 'bool':   return 'boolean';
                    default: throw new ParserException("Тип [$type->class] не является скалярным типом");
                }
            }
            else
            {
                if(is_string($type->class)) {
                    return 'string';
                }
                
                $fixNameSpace = str_replace('\\', '.', $type->class->namespace);
                return $fixNameSpace . '.' . $type->class->name;
            }
        }
        
        return 'any';
    }
}