<?php

namespace LookPhp\Type\Exceptions;

use LookPhp\Exceptions\InvalidArgumentException;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class NumericException extends InvalidArgumentException
{
    const argumentErrMessage = 'not numeric';
}