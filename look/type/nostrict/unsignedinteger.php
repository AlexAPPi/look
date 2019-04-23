<?php

namespace Look\Type\NoStrict;

use Type
use Look\Type\UnsignedInteger as StrictUnsignedInteger;

/**
 * Базовый класс не отрицательного целого числа
 */
class UnsignedInteger extends StrictUnsignedInteger
{    
    /** {@inheritdoc} */
    public function setValue($value) : void
    {
        // Преобразуем строку в значение
        if(is_string($value)) {
            $value = TypeManager::strToNumeric($value);
            if($value !== false) {
                $value = (int)$value;
            }
        }
        
        // Передано значение double
        else if(is_double($value) || is_bool($value)) {
            $value = (int)$value;
        }
        
        parent::setValue($value);
    }
}