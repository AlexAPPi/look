<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\DocBlock;
use Look\API\Parser\TypeScript\TSExporter;
use Look\API\Parser\TypeScript\TSInterfaceMethod;
use Look\API\Parser\TypeScript\TSInterfaceProperty;

/**
 * Интерфейс
 */
class TSInterface extends TSExporter
{
    /** @var string */
    public $name;
    
    /** @var TSInterfaceProperty[] */
    protected $propertys = [];
    
    /** @var TSInterfaceMethod[] */
    protected $methods   = [];
    
    /**
     * Формирует новый интерфейс
     * @param string   $name      -> Название
     * @param DocBlock $desc      -> Описание
     */
    public function __construct(string $name, ?DocBlock $desc = null)
    {
        $this->desc      = $desc;
        $this->name      = $name;
        $this->propertys = [];
        $this->methods   = [];
    }
    
    /** {@inheritdoc} */
    public function getImportList() : array
    {
        $result = [];
        
        $propertysCount = count($this->propertys);
        if($propertysCount > 0) {
            foreach($this->propertys as $prop) {
                $result = array_merge(
                    $result,
                    $prop->getImportList()
                );
            }
        }
        
        $methodsCount = count($this->methods);
        if($methodsCount > 0) {
            foreach($this->methods as $method) {
                $result = array_merge(
                    $result,
                    $method->getImportList()
                );
            }
        }
        
        return $result;
    }
    
    /**
     * @param TSInterfaceMethod $method -> Метод
     */
    public function addMethod(TSInterfaceMethod $method)
    {
        $this->methods[] = $method;
    }
    
    /**
     * Добавляет новое свойство
     * 
     * @param TSInterfaceProperty $property -> Свойство
     */
    public function addProperty(TSInterfaceProperty $property)
    {
        $this->propertys[] = $property;
    }
    
    /** {@inheritdoc} */
    protected function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : string
    {
        $fixName = $this->name;
        $desc    = $this->buildDesc($mainTabStr);
        
        $props   = null;
        $methods = null;
        
        if(count($this->propertys) > 0) {
            $props = '';
            foreach($this->propertys as $prop) {
                $props .= $prop->toTS($offset + 1, $tabSize);
            }
        }
        
        if(count($this->methods) > 0) {
            $methods = '';
            foreach($this->methods as $method) {
                $methods .= $method->toTS($offset + 1, $tabSize);
            }
        }
        
        return
        $desc .
        $mainTabStr . "export interface $fixName\n" .
        $mainTabStr . "{\n" .
        $props .
        $methods .
        $mainTabStr . "}\n";
    }
}