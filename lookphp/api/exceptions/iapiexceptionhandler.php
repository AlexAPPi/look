<?php

namespace LookPhp\API\Exceptions;

use Throwable;

interface IAPIExceptionHandler extends Throwable
{
    public function render($request, Throwable $exception);
}
