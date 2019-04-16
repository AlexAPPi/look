<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class NumericException extends InvalidArgumentException
{
    const argumentErrMessage = 'not numeric';
}