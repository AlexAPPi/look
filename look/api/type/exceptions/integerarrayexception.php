<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class IntegerArrayException extends ArrayException
{
    const argumentErrMessage = 'not integer array';
}