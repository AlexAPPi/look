<?php

namespace Look\Type\Exceptions;

/**
 *  Класс исключения, связанного с неправильной передачей параметра
 */
class EMailException extends InvalidArgumentException
{
    const argumentErrMessage = 'not email';
}