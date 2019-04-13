<?php

namespace LookPhp\API\Parser\TypeScript;

use LookPhp\API\Parser\DocBlock;
use LookPhp\API\Parser\TypeScript\TSClass;
use LookPhp\API\Parser\TypeScript\TSExporter;

use LookPhp\API\Parser\Exceptions\ParserException;

class TSNamespace extends TSExporter
{    
    /** @var string Назание */
    public $name;
    
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
        
        $this->classes    = [];
        $this->interfaces = [];
    }
    
    /**
     * Провереят, существует ли в данной области объект с таким именем
     * @param string $name -> Название
     * @return bool
     */
    public function has(string $name) : bool
    {
        return isset($this->classes[$name])
            || isset($this->interfaces[$name]);
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
     * Добавляет интерфейс в данную область
     * @param TSInterface $interface -> Интерфейс
     * @return void
     */
    public function addInterface(TSInterface $interface) : void
    {
        if(isset($this->classes[$interface->name])) {
            throw new ParserException("Попытка перезаписать класс [$interface->name] который уже уже объявлен в [$this->name]");
        }
        
        if(isset($this->interfaces[$interface->name])) {
            throw new ParserException("Интерфейс [$interface->name] уже объявлен в [$this->name]");
        }
        
        $this->interfaces[$interface->name] = $interface;
    }
        
    /**
     * Добавляет новый класс в данную область
     * @param TSClass $class
     * @return void
     */
    public function addClass(TSClass $class) : void
    {
        if(isset($this->interfaces[$class->name])) {
            throw new ParserException("Попытка перезаписать интерфейс [$class->name] который уже объявлен в [$this->name]");
        }
        
        if(isset($this->classes[$class->name])) {
            throw new ParserException("Класс [$class->name] уже объявлен в [$this->name]");
        }
        
        $this->classes[$class->name] = $class;
    }
    
    /** {@inheritdoc} */
    protected function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : string
    {
        $fixNameSpace = str_replace('\\', '.', $this->name);
        $desc         = $this->buildDesc($mainTabStr);
        
        $interfaces = null;
        $classes    = null;
        
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
        $interfaces .
        $classes    .
        $mainTabStr . "}\n";
    }
}