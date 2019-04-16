<?php

/**
 * Преобразует значение для sql запроса
 * 
 * + Защита от инъекций
 * 
 * @param string   $value   -> значение
 * @param callable $dbQuote -> функция экранирования строки
 * @param bool     $quote   -> помещает строку в ковычки (если тип string), если передан массив, помещает его в круглые скобки (по умолчанию true)
 * 
 * @return array значение или <b>FALSE</b> если возникла ошибка преобразования
 */
function toSql($value, callable $dbQuote = null, $quote = true)
{        
    if($value instanceof DateTime) {

        //if ($value->format('H') === '0') { }

        return '"' . $value->format('Y-m-d H:i:s') . '"';
    }

    if(is_string($value)) {

        $type = null;
        $tmp  = self::strToNumeric($value, $type);

        if ($tmp !== false) {

            if ($type == self::TDouble) {
                return str_replace(',', '.', (string) $value);
            }

            return $value;
        }

        $tmp = $value;

        if($dbQuote) $tmp = call_user_func($dbQuote, $value);
        if($quote)   return '"' . $tmp . '"';

        return $tmp;
    }

    if (is_array($value)) {

        $count = count($value);
        $array = [];

        for ($index = 0; $index < $count; $index++) {
            $array[] = toSql($value[$index], $dbQuote, $quote);
        }

        if($quote) {
            return '(' . implode(',', $array) . ')';
        }

        return implode(',', $array);
    }

    if (is_int($value))   return $value;
    if (is_float($value)) return str_replace(',', '.', (string) $value);
    if (is_bool($value))  return $value ? 1 : 0;

    return false;
}