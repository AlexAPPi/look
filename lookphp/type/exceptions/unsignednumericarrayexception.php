<?php

namespace LookPhp\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class UnsignedNumericArrayException extends ArrayException
{
    const argumentErrMessage = 'not unsigned numeric array';
}