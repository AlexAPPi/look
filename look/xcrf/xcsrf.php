<?php

namespace Look;

use Look\XCSRF\Handler;

/**
 * Класс обработчик форм запросов
 * защищает форму от многостраничного доступа
 * 
 * @author Alexandr Shamarin <alexsandrshamarin@yandex.ru>
 */
class XCSRF
{
    /** @var string Буфер обмена */
    protected static $buffer = '';
    
    /**
     * Возвращает буфер обмена
     * @return string
     */
    public static function getBuffer() : string
    {
        return static::$buffer;
    }
    
    /**
     * Вытаскивает обработчик из данных сессии
     * @param string $id -> Id обработчика
     * @return array|null
     */
    public static function getFromStore(string $id) : ?array
    {
        $check =
              isset($_SESSION)
           && isset($_SESSION['xcsrf'])
           && isset($_SESSION['xcsrf'][$id]);
         
        if($check) {
            return $_SESSION['xcsrf'][$id];
        }
        
        return null;
    }
    
    /**
     * Проверяет существования обработчика для указанных данных
     * 
     * <b>При успешной проверке происходит удаление записей из базы</b>
     * 
     * При следующей вызове данный обработчик будет не доступен
     * 
     * @param string $id     -> Id обработчика
     * @param string $method -> Метод обработки GET|POST|PUT|DELETE
     * @param string $token  -> Уникальный ключ обработчика
     * @return bool
     */
    public static function check(string $id, string $method, string $token) : bool
    {
        $check =
               isset($_SESSION)
            && isset($_SESSION['xcsrf'])
            && isset($_SESSION['xcsrf'][$id])
            && $_SESSION['xcsrf'][$id]['method'] == $method
            && $_SESSION['xcsrf'][$id]['token'] == $token;
        
        if($check) {
            unset($_SESSION['xcsrf'][$id]);
        }
        
        return $check;
    }
    
    /**
     * Регистрирует обработчик в системе
     * @param string   $id      -> Id обработчика
     * @param string   $method  -> Метод обработки GET|POST|PUT|DELETE
     * @param callable $handler -> Функция обработчика
     * @return \Look\XCSRF\Handler
     */
    public static function setHanler(string $id, string $method, callable $handler) : XCSRFEntity
    {
        static::execHandler($id, $handler);
        $token = static::regInStore($id, $method);
        return new Handler($id, $method, $token);
    }
    
    /**
     * 
     * @param string $id        -> Уникальный идентификатор события
     * @param callable $handler -> Обработчик события
     * 
     * Вывод функции сохраняется в буфер
     */
    protected static function execHandler(string $id, callable $handler)
    {
        $inp = (isset($_POST) && isset($_POST['xcsrf_id'])) ? $_POST :
               ((isset($_GET) && isset($_GET['xcsrf_id'])) ? $_GET : false);
        
        if(($inp !== false && $inp['xcsrf_id'] == $id)
        && (static::check($inp['xcsrf_id'], $inp['xcsrf_method'], $inp['xcsrf_token']))) {
            
            // Вывод данных собираем для вывода в шаблоне
            ob_start();
            $res = $handler($inp);
            if($res !== false) {
                static::$buffer .= ob_get_contents() . $res;
            }
            ob_end_clean();
        }
    }
    
    /**
     * Возвращает уникальный токен, являющийся ключом к обработчику
     * 
     * @param string $id     -> Id обработчика
     * @param string $method -> Метод обработки GET|POST|PUT|DELETE
     * @return string
     */
    protected static function regInStore(string $id, string $method) : string
    {
        if(empty(session_id()))        { session_start(); }
        if(!isset($_SESSION['xcsrf'])) { $_SESSION['xcsrf'] = []; }
        
        $token = md5(uniqid(rand(), true));
        $_SESSION['xcsrf'][$id] = ['method' => $method, 'token' => $token];
        return $token;
    }
}
