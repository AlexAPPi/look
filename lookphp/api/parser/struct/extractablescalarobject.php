<?php

namespace LookPhp\API\Parser\Struct;

use LookPhp\API\Parser\DocBlock;

class ExtractableScalarObject
{
    /** @var string DocBlock|string */
    public $comment;
    
    /** @var string */
    public $namespace;
    
    /** @var string */
    public $class;
    
    /** @var string */
    public $scalarType;
}