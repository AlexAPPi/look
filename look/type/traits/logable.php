<?php

namespace Look\Type\Traits;

/**
 * Позволяет объекту вести лог
 */
trait Logable
{
    /**
     * Тип записи лога (def, error, info, warning, ...)
     * @var string 
     */
    protected $logable_prefix = 'log';
    
    /**
     * Формат даты для названия файла (служит для разделения)
     * @var string 
     */
    protected $logable_period = 'Y_m_d_H_i';
    
    /**
     * Флаг записи лога в общий журнал, если указать <b>FALSE</b> запись будет производиться в отдельный журнал
     * @var bool
     */
    protected $logable_general = true;

    /**
     * Записывает строку в лог файла
     * @param string $text
     */
    private function logable_writeLog(string $text, string $type = 'message')
    {
        if($this->logable_general) {
            $date     = date($this->logable_period);
            $fileName = BASE_DIR . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $this->logable_prefix.'_'.$date.'.txt';
        } else{
            $class    = str_replace('\\', '@', get_class($this));
            $date     = date($this->logable_period);
            $fileName = BASE_DIR . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $this->logable_prefix.'_'.$class.'_'.$date.'.txt';
        }
        
        $class  = get_class($this);
        $string = date('d.m.Y H:i:s').' ['.$class.'@'.$type.'] =>'.$text."\r".PHP_EOL;
        
        if($f = fopen($fileName, 'a+')) {
            fwrite($f, $string);
            return fclose($f);
        }
        return false;
    }
    
    /**
     * Записывает строку в лог как сообщение
     * @param string $text -> Текст лога
     */
    public function log(string $text) { return $this->logable_writeLog($text); }
    
    /**
     * Записывает строку в лог как предупреждение
     * @param string $text -> Текст лога
     */
    public function logWarning(string $text) { return $this->logable_writeLog($text, 'warning'); }
    
    /**
     * Записывает строку в лог как ошибку
     * @param string $text -> Текст лога
     */
    public function logError(string $text) { $this->logable_writeLog($text, 'error'); }
}

