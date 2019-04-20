<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\DocBlock;
use Look\API\Parser\TypeScript\TSEnum;
use Look\API\Parser\TypeScript\TSClass;
use Look\API\Parser\TypeScript\TSExporter;

use Look\API\Parser\Exceptions\ParserException;

/**
 * Name space
 */
class TSNamespace extends TSExporter
{    
    /** @var string Назание */
    public $name;
    
    /** @var TSEnum[] */
    public $enums;
    
    /** @var TSInterface[] */
    public $interfaces;
    
    /** @var TSClass[] */
    public $classes;
    
    /**
     * @param string        $name -> Назание
     * @param DocBlock|null $desc -> Блок описания
     */
    public function __construct(string $name, ?DocBlock $desc = null)
    {
        $this->name = $name;
        $this->desc = $desc;
        
        $this->enums      = [];
        $this->classes    = [];
        $this->interfaces = [];
    }
    
    /** {@inheritdoc} */
    public function getImportList() : array
    {
        $result = [];
        
        $enumsCount = count($this->enums);
        if($enumsCount > 0) {
            foreach($this->enums as $enum) {
                $result = array_merge(
                    $result,
                    $enum->getImportList()
                );
            }
        }
        
        $interfacesCount = count($this->interfaces);
        if($interfacesCount > 0) {
            foreach($this->interfaces as $interface) {
                $result = array_merge(
                    $result,
                    $interface->getImportList()
                );
            }
        }
        
        $classesCount = count($this->classes);
        if($classesCount > 0) {
            foreach($this->classes as $class) {
                $result = array_merge(
                    $result,
                    $class->getImportList()
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Провереят, существует ли в данной области объект с таким именем
     * @param string $name -> Название
     * @return bool
     */
    public function has(string $name) : bool
    {
        return isset($this->classes[$name])
            || isset($this->enums[$name])
            || isset($this->interfaces[$name]);
    }
    
    /**
     * Провереят, существует ли в данной области объект enum с таким именем
     * @param string $name -> Назание
     * @return bool
     */
    public function hasEnum(string $name) : bool
    {
        return isset($this->enums[$name]);
    }
    
    /**
     * Провереят, существует ли в данной области объект с таким именем
     * @param string $name -> Назание
     * @return bool
     */
    public function hasClass(string $name) : bool
    {
        return isset($this->classes[$name]);
    }

    /**
     * Провереят, существует ли в данной области объект с таким именем
     * @param string $name -> Назание
     * @return bool
     */
    public function hasInterface(string $name) : bool
    {
        return isset($this->interfaces[$name]);
    }
    
    /**
     * Добавляет enum в данную область
     * @param TSEnum $enum -> Enum
     * @return void
     */
    public function addEnum(TSEnum $enum) : void
    {
        $name = $enum->name;
        
        if($this->hasInterface($name)) {
            throw new ParserException("Попытка перезаписать интерфейс [$name] который уже уже объявлен в [$this->name]");
        }
        
        if($this->hasClass($name)) {
            throw new ParserException("Попытка перезаписать класс [$name] который уже уже объявлен в [$this->name]");
        }
        
        if($this->hasEnum($name)) {
            throw new ParserException("Enum [$name] уже объявлен в [$this->name]");
        }
        
        $this->enums[$name] = $enum;
    }
    
    /**
     * Добавляет интерфейс в данную область
     * @param TSInterface $interface -> Интерфейс
     * @return void
     */
    public function addInterface(TSInterface $interface) : void
    {
        $name = $interface->name;
        
        if($this->hasEnum($name)) {
            throw new ParserException("Попытка перезаписать enum [$name] который уже уже объявлен в [$this->name]");
        }
        
        if($this->hasClass($name)) {
            throw new ParserException("Попытка перезаписать класс [$name] который уже уже объявлен в [$this->name]");
        }
        
        if($this->hasInterface($name)) {
            throw new ParserException("Интерфейс [$name] уже объявлен в [$this->name]");
        }
        
        $this->interfaces[$name] = $interface;
    }
        
    /**
     * Добавляет новый класс в данную область
     * @param TSClass $class
     * @return void
     */
    public function addClass(TSClass $class) : void
    {
        $name = $class->name;
        
        if($this->hasEnum($name)) {
            throw new ParserException("Попытка перезаписать enum [$name] который уже уже объявлен в [$this->name]");
        }
        
        if($this->hasInterface($name)) {
            throw new ParserException("Попытка перезаписать интерфейс [$name] который уже объявлен в [$this->name]");
        }
        
        if($this->hasClass($name)) {
            throw new ParserException("Класс [$name] уже объявлен в [$this->name]");
        }
        
        $this->classes[$name] = $class;
    }
    
    /** {@inheritdoc} */
    protected function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : string
    {
        $fixNameSpace = str_replace('\\', '.', $this->name);
        $desc         = $this->buildDesc($mainTabStr);
        
        $enums      = null;
        $interfaces = null;
        $classes    = null;
        
        $i = 0;
        $enumsCount = count($this->enums);
        if($enumsCount > 0) {
            $enums = '';
            foreach($this->enums as $enum) {
                $i++;
                $enums .= $enum->toTS($offset + 1, $tabSize);
                if($i < $enumsCount) {
                    $enums .= "\n";
                }
            }
        }
        
        $i = 0;
        $interfacesCount = count($this->interfaces);
        if($interfacesCount > 0) {
            $interfaces = '';
            foreach($this->interfaces as $interface) {
                $i++;
                $interfaces .= $interface->toTS($offset + 1, $tabSize);
                if($i < $interfacesCount) {
                    $interfaces .= "\n";
                }
            }
        }
        
        $i = 0;
        $classesCount = count($this->classes);
        if($classesCount > 0) {
            $classes = '';
            foreach($this->classes as $class) {
                $i++;
                $classes .= $class->toTS($offset + 1, $tabSize);
                if($i < $classesCount) {
                    $classes .= "\n";
                }
            }
        }
        
        return
        $desc .
        $mainTabStr . "namespace $fixNameSpace\n" .
        $mainTabStr . "{\n" .
        $enums      .
        $interfaces .
        $classes    .
        $mainTabStr . "}\n";
    }
}