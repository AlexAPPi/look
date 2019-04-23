<?php

namespace Look\Type\Exceptions;

use Look\Exceptions\InvalidArgumentException as LookInvalidArgumentException;

/**
 * Исключение связанное с передачей параметра иного типа
 */
class InvalidArgumentException extends LookInvalidArgumentException
{
    const argumentErrMessage = 'invalid';
}