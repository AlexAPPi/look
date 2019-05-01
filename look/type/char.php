<?php

namespace Look\Type;

use Look\Type\Exceptions\CharException;

/**
 * Тип значения 1 символа
 */
class Char extends Str
{        
    /** {@inheritdoc} */
    public static function __getEvalType(): string { return self::TChar; }
        
    /** {@inheritdoc} */
    public function setValue($value) : void
    {
        if(!is_string($value) || mb_strlen($value) > 1) {
            throw new CharException('value');
        }
        
        $this->value = $value;
    }
}