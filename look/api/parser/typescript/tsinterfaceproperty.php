<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\DocBlock;
use Look\API\Parser\TypeScript\TSType;
use Look\API\Parser\TypeScript\TSValue;
use Look\API\Parser\TypeScript\TSExporter;
use Look\API\Parser\DocBlock\ParamDocBlock;

/**
 * Объект свойства
 */
class TSInterfaceProperty extends TSExporter
{
    /** @var bool Статическое свойство */
    public $isStatic;
    public $name;
    public $type;
    public $default;
    
    /**
     * Новое свойство
     * 
     * @param bool          $static   -> Свойство статичное
     * @param string        $name     -> Название
     * @param TSType        $type     -> Тип
     * @param TSValue|null  $default  -> Значение по умолчанию
     * @param DocBlock|null $desc     -> Описание
     */
    public function __construct(bool $static, string $name, TSType $type, ?TSValue $default, ?DocBlock $desc)
    {
        $this->isStatic = $static;
        $this->name     = $name;
        $this->type     = $type;
        $this->default  = $default;
        $this->desc     = $desc;
    }
    
    /** {@inheritdoc} */
    public function buildDesc(string $mainTabStr, string $tabStr = ''): ?string
    {
        if($this->desc)
        {
            $fixName    = $this->name;
            $extractDoc = $this->desc->param[$fixName];
            if($extractDoc instanceof ParamDocBlock)
            {
                // /*
                //  * {DESC}
                //  */
                return $mainTabStr . $tabStr . "/**\n" .
                       $mainTabStr . $tabStr . " * $extractDoc->desc\n" .
                       $mainTabStr . $tabStr . " */\n";
            }
        }
        
        return null;
    }
    
    /** {@inheritdoc} */
    protected function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : string
    {
        $fixName = $this->name;
        $desc    = $this->buildDesc($mainTabStr);
        
        $strBefore = '';
        if($this->isStatic) {
            $strBefore .= 'static ';
        }
        
        $strAfter = ': ' . $this->type->toTS(0, 0);
        
        return
        $desc . $mainTabStr . "$strBefore$fixName$strAfter;\n";
    }
}