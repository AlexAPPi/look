<?php

namespace Look\Type\Traits;

use SplFixedArray;

/**
 * Позволяет объекту регистрирует изменения значений
 */
trait Changesable
{
    /**
     * @var array Журнал изменений 
     */
    protected $__changesableList = [];
    
    /**
     * Возвращает значения свойств объекта
     * @return type
     */
    private function changesableExtractVars()
    {
        $vars = get_object_vars($this);
        $change = [];
        foreach($vars as $name => $value) {
            // разрешены только буквенные свойства
            if(strpos($name, '__') === false) {
                $change[$name] = $value;
            }
        }
        return $change;
    }
    
    /**
     * Сохраняет изменения значений свойств в хранилище
     * @return $this
     */
    public function changesableSaveChanges()
    {
        $this->__changesableList[] = $this->changesableExtractVars();
        return $this;
    }
    
    /**
     * Устанавливает значения по умолчанию
     * @return $this
     */
    public function changesableInitDefault()
    {
        $this->__changesableList[0] = $this->changesableExtractVars();
        return $this;
    }
    
    /**
     * Возвращает журнал изменений
     * @param int $level -> Уровень записи
     * @return type
     */
    public function changesableGetChangesLevel(int $level = 0)
    {
        return isset($this->__changesableList[$level]) ?
                     $this->__changesableList[$level] :
                     $this->changesableExtractVars();
    }
    
    /**
     * Возвращает базовые значения свойств объекта
     * @param string $name -> Название свойства
     * @return type
     */
    public function changesableGetDefault(string $name = null)
    {
        return isset($name) ?
               $this->changesableGetChangesLevel(0)[$name] :
               $this->changesableGetChangesLevel(0);
    }
    
    /**
     * Возвращает список изменений в виде массива<br>
     * 
     * Возвращает массив в виде [<b>$name</b> => [<b>$currect</b>, <b>$default</b>], ...]
     * 
     * @return array|bool Возвращает <b>FALSE</b>, если изменений нет
     */
    public function changesableGetChanges()
    {
        if(isset($this->__changesableList[0])) {
            
            $change = [];
            foreach($this->__changesableList[0] as $name => $val) {
                if($this->{$name} != $val) {
                    $change[$name] = new SplFixedArray(2);
                    $change[$name][0] = $this->{$name};
                    $change[$name][1] = $val;
                }
            }
            
            return count($change) > 0 ? $change : false;
        }

        return false;
    }
    
    /**
     * Возвращает статус изменения значения
     * @param string $name -> Название свойства
     * @return type
     */
    public function changesableIsChange(string $name = null) : bool
    {
        if(isset($this->__changesableList[0])) {
            if($name) {
                return $this->{$name} == $this->__changesableList[0][$name];
            } else {
                foreach($this->__changesableList[0] as $name => $val) {
                    if($this->{$name} != $val) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}

