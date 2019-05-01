<?php

namespace Look\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class VarCharException extends InvalidArgumentException
{
    const argumentErrMessage = 'not varchar';
}