<?php

namespace Look\API\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class DoubleArrayException extends ArrayException
{
    const argumentErrMessage = 'not double array';
}