<?php

namespace LookPhp\Type\Traits;

use LookPhp\Type\Traits\Bundleble;

use ReflectionClass;
use ReflectionProperty;

/**
 * Позволяет объету перехватывать обращения к свойствам
 */
trait GSable
{
    use Bundleble;
    
    protected $__gsUse = false;
    protected $__gsSetBuffer;
    
    public function getProxy()
    {
        
    }
    
    public function &__get(string $name)
    {
        $this->execBundle('get' . $name);
        return;
    }
    
    public function __set(string $name, &$value)
    {
        $this->__gsSetBuffer = &$value;
        $this->execBundle('set' . $name);
    }
}
