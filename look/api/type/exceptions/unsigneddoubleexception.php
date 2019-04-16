<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class UnsignedDoubleException extends UnsignedNumericException
{
    const argumentErrMessage = 'not unsigned double';
}