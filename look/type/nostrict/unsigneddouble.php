<?php

namespace Look\Type\NoStrict;

use Look\Type\TypeManager;
use Look\Type\UnsignedDouble as StrictUnsigneDouble;

/**
 * Базовый класс не отрицательного числа
 */
class UnsignedDouble extends StrictUnsigneDouble
{
    /** {@inheritdoc} */
    public function setValue($value) : void
    {
        // Преобразуем строку в значение
        if(is_string($value)) {
            $value = TypeManager::strToNumeric($value);
            if($value !== false) {
                $value = (float)$value;
            }
        }
        
        // Передано значение double
        else if(is_int($value) || is_bool($value)) {
            $value = (float)$value;
        }
        
        parent::setValue($value);
    }
    
    /** {@inheritdoc} */
    public static function __getEvalType(): string { return self::TUnsignedDouble; }
    
    /** {@inheritdoc} */
    public static function __getSystemEvalType() : string { return self::TDouble; }
}