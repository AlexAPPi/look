<?php

namespace Look\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class CallableException extends InvalidArgumentException
{
    const argumentErrMessage = 'not callable';
}