<?php

namespace Look\Type\NoStrict;

use Look\Type\TypeManager;
use Look\Type\Interfaces\INotStrict;
use Look\Type\Numeric as StrictNumeric;

/**
 * Значение типа integer или double
 */
class Numeric extends StrictNumeric implements INotStrict
{
    /** {@inheritdoc} */
    public function setValue($value): void
    {
        // Преобразуем строку в значение
        if(is_string($value)) {
            $value = TypeManager::strToNumeric($value);
        }
        else if(is_bool($value)) {
            $value = (int)$value;
        }
        
        parent::setValue($value);
    }
}