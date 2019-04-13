<?php

namespace LookPhp\Exceptions;

use Exception;
use Throwable;

/**
 * Базовый 
 */
trait SystemExceptionTrait
{    
    /**
     * @var int Радиус фрагмента кода
     */
    protected $contextRadius = 5;
    
    /**
     * @var Exception Предыдушее исключение
     */
    protected $prevException = null;
    
    /**
     * @var int Преобразует все возможные символы в соответствующие HTML-сущности
     */
    protected static $htmlspecialcharsMode = ENT_NOQUOTES;

    /**
     * @param null|string|Throwable $message  Сообщение исключения
     * @param null|int|Throwable    $code     Код исключения
     * @param null|Throwable        $previous Предыдущие исключения
     */
    protected function __initException(&$message = '', &$code = 500, &$previous = null)
    {
        if ($message instanceof Throwable) {
            $previous = $message;
            $code     = $previous->getCode();
            $message  = $previous->getMessage();
        } else if ($code instanceof Throwable) {
            $previous = $code;
            $code     = $previous->getCode();
        }
                
        $this->prevException = $previous;
        
        if (defined('ENT_SUBSTITUTE')) {
            self::$htmlspecialcharsMode |= ENT_SUBSTITUTE;
        } else if (defined('ENT_IGNORE')) {
            self::$htmlspecialcharsMode |= ENT_IGNORE;
        }
    }
    
    /**
     * Returns previous Exception
     * <p>Returns previous exception (the third parameter of <code>Exception::__construct()</code>).</p>
     * @return Throwable <p>Returns the previous Throwable if available or <b><code>NULL</code></b> otherwise.</p>
     * @link http://php.net/manual/en/exception.getprevious.php
     * @since PHP 5 >= 5.3.0, PHP 7
     */
    public function getPrevException()
    {
        return $this->prevException;
    }
    
    /**
     * Gets the stack trace as a string
     * <p>Returns the Exception stack trace as a string.</p>
     * @return string <p>Returns the Exception stack trace as a string.</p>
     * @link http://php.net/manual/en/exception.gettraceasstring.php
     * @since PHP 5, PHP 7
     */
    public function getFullTraceAsString() : string
    {
        $result = '## '.$this->getFile().'('.$this->getLine().")" . PHP_EOL;
        $result .= $this->getTraceAsString();
        if ($this->prevException) {
            $msg = $this->prevException->getMessage();
            $result .= PHP_EOL . PHP_EOL . "Next ".get_class($this->prevException)." with message '".$msg."':" . PHP_EOL;
            if (instanceofTrait($this->prevException, SystemExceptionTrait::class)) {
                $result .= $this->prevException->getFullTraceAsString();
            } else {
                $result .= '## '.$this->prevException->getFile().'('.$this->prevException->getLine().")" . PHP_EOL;
                $result .= $this->prevException->getTraceAsString();
            }
        }
        return $this->hideRootPath($result);
    }
    
    /**
     * Обрезает абсолютные пути на относительные
     * @param string $str Строка
     * @return string
     */
    protected function hideRootPath(string $str) : string
    {
        $root_path = realpath(dirname(__FILE__).'/..');
        $root_path = str_replace('\\', '/', $root_path);
        $root_path = preg_quote($root_path, '~');
        $str = str_replace('\\', '/', $str);
        return preg_replace("~(^|\s){$root_path}/?~", '$1', $str);
    }
    
    /**
     * Возвращает фрагмент кода, где произошла ошибка
     * @return string
     */
    public function getFileContext() : string
    {
        return get_file_context_radius($this->getFile(), $this->getLine(), $this->contextRadius);
    }
}