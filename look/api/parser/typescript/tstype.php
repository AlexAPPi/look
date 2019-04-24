<?php

namespace Look\API\Parser\TypeScript;

use Look\Type\TypeManager;
use Look\Type\Interfaces\IType;

use Look\API\Parser\Struct\Type;
use Look\API\Parser\TypeScript\TSExporter;
use Look\API\Parser\Exceptions\ParserException;

use Look\API\Parser\Struct\ExtractableEnum;
use Look\API\Parser\Struct\ExtractableScalarArray;
use Look\API\Parser\Struct\ExtractableScalarObject;

class TSType extends TSExporter
{
    public $type;
    
    /**
     * @param mixed $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }
    
    /** {@inheritdoc} */
    public function getImportList() : array
    {
        $result = [];
        $type   = $this->type;
        
        if($type instanceof Type) {
            
            if(!$type->isScalar) {
                
                if(!is_string($type->class)
                && !$type->class instanceof ExtractableScalarArray
                && !$type->class instanceof ExtractableScalarObject) {
                    
                    $result[] = $type->class->namespace . '\\' . $type->class->name;
                }
            }
        }
            
        return $result;
    }
    
    /**
     * Возвращает тип TS соответствующий IType стандарту
     * @param string $name
     */
    protected function getTSTypeForITypeStanart(string $name) : string
    {
        switch($name)
        {
            case IType::TMixed:  return 'any';
            case IType::TObject: return 'Object';
            case IType::TArray:  return 'Array<any>';
            case IType::TBool:   return 'boolean';
            
            case IType::TScalar:
            case IType::TString: return 'string';
            
            case IType::TNumeric:
            case IType::TInteger:
            case IType::TDouble:
            case IType::TUnsignedNumeric:
            case IType::TUnsignedInteger:
            case IType::TUnsignedDouble: return 'number';
            
            case IType::TStringArray:
            case IType::TScalarArray:  return 'Array<string>';
                
            case IType::TBoolArray:    return 'Array<boolean>';
            
            case IType::TNumericArray:
            case IType::TIntegerArray:
            case IType::TDoubleArray:
            case IType::TUnsignedNumericArray:
            case IType::TUnsignedIntegerArray:
            case IType::TUnsignedDoubleArray: return 'Array<number>';
            
            default : return null;
        }
    }


    /** {@inheritdoc} */
    protected function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : string
    {
        $type = $this->type;
        
        if($type instanceof Type) {
            
            if($type->isScalar) {
                
                $fixType = $this->getTSTypeForITypeStanart($type->class);
                if($fixType === null) {
                    throw new ParserException("Тип [$type->class] не является скалярным типом");
                }
                return $fixType;
            }
            else {
                
                if(is_string($type->class)) {
                    return 'string';
                }
                
                if($type->class instanceof ExtractableScalarObject) {
                    $fixType = $this->getTSTypeForITypeStanart($type->class->scalarType);
                    if($fixType === null) {
                        throw new ParserException("Тип [$type->class] не является скалярным типом");
                    }
                    return $fixType;
                }
                
                if($type->class instanceof ExtractableScalarArray) {
                    $arrType = TypeManager::getArrayTypeFor($type->class->scalarType);
                    $fixType = $this->getTSTypeForITypeStanart($arrType);
                    if($fixType === null) {
                        throw new ParserException("Тип [$type->class] не является массивом состоящим из скалярных типом");
                    }
                    return $fixType;
                }
                
                return $type->class->name;
                
                //$fixNameSpace = str_replace('\\', '.', $type->class->namespace);            
                //return $fixNameSpace . '.' . $type->class->name;
            }
        }
        
        return 'any';
    }
}