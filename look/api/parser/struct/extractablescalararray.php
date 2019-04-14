<?php

namespace Look\API\Parser\Struct;

class ExtractableScalarArray
{
    /** @var string DocBlock|string */
    public $comment;
    
    /** @var string */
    public $namespace;
    
    /** @var string */
    public $arrayClass;
    
    /** @var string */
    public $scalarType;
}