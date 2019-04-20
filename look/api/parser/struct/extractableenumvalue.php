<?php

namespace Look\API\Parser\Struct;

class ExtractableEnumValue
{
    /** @var string DocBlock|string */
    public $comment;
    
    /** @var string */
    public $name;
    
    /** @var mixed */
    public $value;
}