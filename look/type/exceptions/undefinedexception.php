<?php

namespace Look\Type\Exceptions;

use Look\Exceptions\InvalidArgumentException;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class UndefinedException extends InvalidArgumentException
{
    const argumentErrMessage = 'undefined';
}