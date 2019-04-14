<?php

namespace Look\Type\Exceptions;

use Look\Exceptions\InvalidArgumentException;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class StringException extends InvalidArgumentException
{
    const argumentErrMessage = 'not string';
}