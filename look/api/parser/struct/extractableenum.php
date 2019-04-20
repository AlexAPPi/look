<?php

namespace Look\API\Parser\Struct;

class ExtractableEnum
{
    /** @var string DocBlock|string */
    public $comment;
    
    /** @var string */
    public $namespace;
    
    /** @var string */
    public $name;
    
    /** @var ExtractableEnumValues[] */
    public $values;
}