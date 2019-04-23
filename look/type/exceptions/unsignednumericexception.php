<?php

namespace Look\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class UnsignedNumericException extends NumericException
{
    const argumentErrMessage = 'not unsigned numeric';
}