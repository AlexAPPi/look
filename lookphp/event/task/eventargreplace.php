<?php

namespace LookPhp\Event\Task;

use LookPhp\Event\Interfaces\IEventArgReplace;

/**
 * Позволяет выполнять операции подмены аргументов
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class EventArgReplace extends EventTask implements IEventArgReplace
{
    protected $originalArgument = [];
    protected $replaceArgument = [];
    
    /**
     * @param array $arguments Оригинальные аргументы
     */
    public function __construct(?array $arguments = null)
    {
        if($arguments === null) {
            $arguments = [];
        }
        
        $this->originalArgument = $arguments;
        $this->replaceArgument  = $arguments;
        
        parent::__construct('', 0, null);
    }
    
    /** {@inheritdoc} */
    public function getOriginalArgument() : array
    {
        return $this->originalArgument;
    }
    
    /** {@inheritdoc} */
    public function setOriginalArgument(array $arguments) : void
    {
        $this->originalArgument = $arguments;
    }
    
    /** {@inheritdoc} */
    public function getOriginalArgumentByIndex(int $index)
    {
        return $this->originalArgument[$index];
    }
    
    /** {@inheritdoc} */
    public function setOriginalArgumentByIndex(int $index, $value) : void
    {
        $this->originalArgument[$index] = $value;
    }
    
    /** {@inheritdoc} */
    public function getReplaceArgument() : array
    {
        return $this->replaceArgument;
    }
    
    /** {@inheritdoc} */
    public function setReplaceArgument(array $arguments) : void
    {
        $this->replaceArgument = $arguments;
    }
    
    /** {@inheritdoc} */
    public function getReplaceArgumentByIndex(int $index)
    {
        return $this->replaceArgument[$index];
    }
    
    /** {@inheritdoc} */
    public function setReplaceArgumentByIndex(int $index, $value) : void
    {
        $this->replaceArgument[$index] = $value;
    }
}