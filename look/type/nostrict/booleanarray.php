<?php

namespace Look\Type\NoStrict;

use Look\Type\TypeManager;
use Look\Type\Interfaces\INotStrict;
use Look\Type\BooleanArray as StrictBooleanArray;

/**
 * Базовый класс массива состоящего только из boolean
 */
class BooleanArray extends StrictBooleanArray implements INotStrict
{
    /** {@inheritdoc} */
    public static function convertOffsetValue($value)
    {
        return TypeManager::anyToBool($value);
    }
}
