<?php

namespace Look\Type\NoStrict;

use Look\Type\TypeManager;
use Look\Type\Interfaces\INotStrict;
use Look\Type\UnsignedNumeric as StrictUnsignedNumeric;

/**
 * Базовый класс не отрицательного числа с плавающей точкой
 */
class UnsignedNumeric extends StrictUnsignedNumeric implements INotStrict
{
    /** {@inheritdoc} */
    public function setValue($value) : void
    {
        if(is_string($value)) {
            $fixValue = TypeManager::strToNumeric($value);
            if($fixValue !== false && $fixValue >= 0) {
                $value = $fixValue;
            }
        }
        else if(is_bool($value)) {
            $value = (int)$value;
        }
        
        parent::setValue($value);
    }
}