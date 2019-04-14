<?php

namespace Look\Layout;

class FileBuilder
{
    /**
     * Проверяет является ли данный путь локальным
     * 
     * @param string $path
     * @return boolean|string
     */
    public static function isLocal($path)
    {
        if ($path[0] == '/' || $path[0] == '\\') {
            return substr($path, 1);
        }

        return false;
    }

    /**
     * Возвращает полный путь к файлу
     * 
     * @param   string  $fileName -> Возвращает готовый адрес для файла
     * @return  string            -> Путь к файлу
     */
    public static function getPath($fileName)
    {
        if (strpos($fileName, BASE_DIR) === 0) {

            return $fileName;
        }
        
        $newFile = self::isLocal($fileName);

        if ($newFile !== false) {

            $fileName = $newFile;
        }

        return BASE_DIR . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Подключает файл
     * 
     * @param string $file   -> путь к файлу
     * @param array  $values -> значения, с развернутыми названиями
     * @return string
     */
    public static function get($file, $values = null)
    {
        // Извлекаем переменные которые нужно сохранить в этой области
        if (!empty($values)) {
            extract($values, EXTR_OVERWRITE);
        }

        $path = self::getPath($file);

        ob_start();
        require $path;
        $tmp = ob_get_contents();
        ob_end_clean();

        return $tmp;
    }
}