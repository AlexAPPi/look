<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class UnsignedIntegerException extends UnsignedNumericException
{
    const argumentErrMessage = 'not unsigned integer';
}