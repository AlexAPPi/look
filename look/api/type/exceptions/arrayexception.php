<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class ArrayException extends InvalidArgumentException
{
    const argumentErrMessage = 'not array';
}