<?php

namespace Look\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class EnumException extends InvalidArgumentException
{
    const argumentErrMessage = 'not enum';
}