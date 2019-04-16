<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class StringException extends InvalidArgumentException
{
    const argumentErrMessage = 'not string';
}