<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class IntegerException extends InvalidArgumentException
{
    const argumentErrMessage = 'not integer';
}