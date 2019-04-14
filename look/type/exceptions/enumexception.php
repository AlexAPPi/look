<?php

namespace Look\Type\Exceptions;

use Look\Exceptions\InvalidArgumentException;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class EnumException extends InvalidArgumentException
{
    const argumentErrMessage = 'not enum';
}