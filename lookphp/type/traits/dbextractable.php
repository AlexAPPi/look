<?php

namespace LookPhp\Type\Traits;

use LookPhp\DB;

/**
 * Позволяет объекту работать с базой данных
 */
trait DBExtractable
{
    /**
     * Возвращает 1 элемент
     * @param type $sql
     * @return type
     */
    protected function extractDbSignle($sql)
    {
        $res = DB::query($sql);
        
        if($res) return DB::fetchAssoc($res);
        
        return false;
    }
    
    /**
     * Формирует массив по ключу id
     * @param type $sql
     * @return type
     */
    protected function extractDbById($sql)
    {
        $result = [];
        $res   = DB::query($sql);

        while($tmp = DB::fetchAssoc($res)) {
            $result[$tmp['id']] = $tmp;
        }
        return $result;
    }
    
    /**
     * Формирует массив ключей id
     * @param type $sql
     * @return type
     */
    protected function extractDbId($sql) {
        
        $result = [];
        $res   = DB::query($sql);

        while($tmp = DB::fetchAssoc($res)) {
            $result[] = $tmp['id'];
        }
        return $result;
    }
    
    /**
     * Вернет количесто элементов из запроса
     * @param type $sql
     * @return int
     */
    protected function extractDbCount($sql)
    {
        $res = DB::query('select count(*) as count ' . $sql);
        if($tmp = DB::fetchAssoc($res)) {
            return (int)$tmp['count'];
        }
        return 0;
    }
        
    /**
     * Комбинирует данные по ID ключу
     * @param type $array
     * @param type $values
     * @param type $entity
     * @param type $name
     * @return type
     */
    protected function combineArrayByIdOfKey($array, $values, $entity, $name) {

        $result     = [];
        $ids        = array_keys($array);
        $count      = count($ids);
        $combineKey = $entity . '_id';

        for($i = 0; $i < $count; $i++) {

            $currectId  = $ids[$i];
            $rowCurrect = $array[$currectId];
            $combineVal = [];

            foreach($values as $subId => $value) {
                if(isset($value[$combineKey]) && $value[$combineKey] == $currectId) {
                    $combineVal[$subId] = $value;
                }
            }

            $rowCurrect[$name] = $combineVal;
            $result[$currectId] = $rowCurrect;
        }

        return $result;
    }
}

