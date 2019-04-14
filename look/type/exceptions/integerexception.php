<?php

namespace Look\Type\Exceptions;

use Look\Exceptions\InvalidArgumentException;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class IntegerException extends InvalidArgumentException
{
    const argumentErrMessage = 'not integer';
}