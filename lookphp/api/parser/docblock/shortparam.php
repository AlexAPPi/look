<?php

namespace LookPhp\API\Parser\DocBlock;

class ShortParam
{
    public $param;
    public $type;
    public $desc;
    
    public function __construct(string $param, string $type, string $desc)
    {
        $this->param = $param;
        $this->type  = $type;
        $this->desc  = $desc;
    }
}