<?php

namespace Look\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class UnsignedIntegerArrayException extends UnsignedNumericArrayException
{
    const argumentErrMessage = 'not unsigned integer array';
}