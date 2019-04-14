<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\DocBlock;
use Look\API\Parser\TypeScript\TSMethod;
use Look\API\Parser\TypeScript\TSArgumentList;

/**
 * Конструктор класса
 */
class TSConstructor extends TSMethod
{
    /**
     * Конструктор класса
     * @param TSArgumentList $arguments -> Аргументы
     * @param DocBlock|null  $desc      -> Описание
     */
    public function __construct(TSArgumentList $arguments, ?DocBlock $desc = null)
    {
        parent::__construct('constructor', $arguments, TSMethod::NoPrefix, TSMethod::PublicAccess, $desc);
    }
    
    public function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr): string
    {
        $fixName   = $this->name;
        $desc      = $this->buildDesc($mainTabStr);
        $arguments = $this->arguments->toTS();
        
        return
        $desc .
        $mainTabStr . $tabStr . "$fixName($arguments) {}\n";
    }
}

