<?php

namespace LookPhp\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class BooleanArrayException extends ArrayException
{
    const argumentErrMessage = 'not bool array';
}