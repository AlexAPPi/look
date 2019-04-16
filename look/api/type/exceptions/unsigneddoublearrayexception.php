<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class UnsignedDoubleArrayException extends UnsignedNumericArrayException
{
    const argumentErrMessage = 'not unsigned double array';
}