<?php

namespace Look\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class ModelException extends InvalidArgumentException
{
    const argumentErrMessage = 'not model';
}