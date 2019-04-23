<?php

namespace Look\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class CharException extends InvalidArgumentException
{
    const argumentErrMessage = 'not char';
}