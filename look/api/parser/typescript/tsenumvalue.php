<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\DocBlock;

/**
 * Преобразует объект
 */
class TSEnumValue extends TSExporter
{
    public $name;
    public $value;
    
    /**
     * Значение Enum
     * @param string                                    $name  -> Название
     * @param \Look\API\Parser\TypeScript\TSValue       $value -> Значение
     * @param \Look\API\Parser\TypeScript\DocBlock|null $desc  -> Описание
     */
    public function __construct(string $name, TSValue $value, ?DocBlock $desc)
    {
        $this->name  = $name;
        $this->value = $value;
        $this->desc  = $desc;
    }
    
    /** {@inheritdoc} */
    public function getImportList(): array
    {
        return $this->value->getImportList();
    }
    
    /** {@inheritdoc} */
    public function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr): string
    {
        $desc = $this->buildDesc($mainTabStr, $tabStr);
        return
        $desc .
        $mainTabStr . $tabStr . "$this->name = {$this->value->toTS(0,0)}";
    }
}