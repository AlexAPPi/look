<?php

namespace LookPhp\API\Parser\TypeScript;

use LookPhp\API\Parser\TypeScript\TSType;
use LookPhp\API\Parser\TypeScript\TSValue;
use LookPhp\API\Parser\TypeScript\TSArgument;

/**
 * Объект аргумента
 */
class TSConstructorArgument extends TSArgument
{
    const NoPrefix        = 255;
    const PublicAccess    = 256;
    const ProtectedAccess = 257;
    const PrivateAccess   = 258;
    
    public $access;
    
    /**
     * Аргумент
     * 
     * @param int    $access
     * @param string $name
     * @param mixed  $type
     * @param mixed  $default
     * @param bool   $required
     * @param bool   $variadic
     * @param int    $position
     */
    public function __construct(int $access, string $name, TSType $type, TSValue $default, bool $required, bool $variadic, int $position)
    {
        $this->access   = $access;
        parent::__construct($name, $type, $default, $required, $variadic, $position);
    }
    
    /** {@inheritdoc} */
    public function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : string
    {
        $str    = parent::buildTS(0, 0, '', '');
        $access = null;
        switch($this->access) {
            case static::ProtectedAccess: $access = 'protected '; break;
            case static::PrivateAccess: $access = 'private '; break;
            case static::PublicAccess: $access = 'public '; break;
            default: break;
        }
        return $mainTabStr . $tabStr . $access . $str;
    }
}