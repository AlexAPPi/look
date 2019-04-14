<?php

namespace Look\Layout;

use Look\Layout\FileBuilder;

/**
 * Обработчик макетов
 */
class LayoutBuilder
{
    const type = 'php';
    
    /**
     * Возвращает собранный макет
     * 
     * @param string $name 
     * @param array  $vars
     * @return string
     */
    public static function get(string $name, array $vars)
    {
        return FileBuilder::get(LAYOUT_DIR . DIRECTORY_SEPARATOR . $name . '.' . self::type, $vars);
    }
}