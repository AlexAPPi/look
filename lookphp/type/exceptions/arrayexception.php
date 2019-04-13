<?php

namespace LookPhp\Type\Exceptions;

use LookPhp\Exceptions\InvalidArgumentException;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class ArrayException extends InvalidArgumentException
{
    const argumentErrMessage = 'not array';
}