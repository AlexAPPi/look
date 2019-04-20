<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\DocBlock;

/**
 * Enum структура
 */
class TSEnum extends TSExporter
{
    public $name;
    public $values;
    
    /**
     * 
     * @param string $name
     * @param DocBlock|null $desc
     * @param \Look\API\Parser\TypeScript\TSEnumValue $value
     */
    public function __construct(string $name, ?DocBlock $desc, TSEnumValue ...$value)
    {
        $this->name   = $name;
        $this->desc   = $desc;
        $this->values = $value;
    }
    
    /** {@inheritdoc} */
    public function getImportList(): array
    {
        return [];
    }
    
    /** {@inheritdoc} */
    public function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr): string
    {
        $fixName = $this->name;
        $desc    = $this->buildDesc($mainTabStr);
        
        $i = 0;
        $valuesCount = count($this->values);
        if($valuesCount > 0) {
            $values = '';
            foreach($this->values as $value) {
                $i++;
                $values .= $value->toTS($offset, $tabSize);
                if($i < $valuesCount) {
                    $values .= ",\n";
                }
            }
            $values .= "\n";
        }
        
        return
        $desc .
        $mainTabStr . "export enum $fixName\n" .
        $mainTabStr . "{\n" .
        $values .
        $mainTabStr . "}\n";
    }
}