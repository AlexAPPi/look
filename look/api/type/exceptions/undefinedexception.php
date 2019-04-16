<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class UndefinedException extends InvalidArgumentException
{
    const argumentErrMessage = 'undefined';
}