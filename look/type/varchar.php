<?php

namespace Look\Type;

use Look\Type\Exceptions\VarCharException;

/**
 * Тип значения от 1 до 255 символов
 */
class VarChar extends Str
{
    /** {@inheritdoc} */
    public static function __getEvalType(): string { return self::TVarChar; }
    
    /** {@inheritdoc} */
    public function setValue($value) : void
    {
        if(!is_string($value) || mb_strlen($value) > 255) {
            throw new VarCharException('value');
        }
        
        $this->value = $value;
    }
}