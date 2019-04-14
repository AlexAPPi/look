<?php

namespace Look\API\Parser\Struct;

use Look\API\Parser\DocBlock;

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