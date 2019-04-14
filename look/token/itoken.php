<?php

namespace Look\Token;

use Look\Token\DB\ITokenDataBase;

/**
 * Объект токена
 */
interface IToken
{
    /**
     * Конструктор класса
     * 
     * @param ITokenDataBase  $db
     * @param string|int|null $dbId
     * @param string          $hex
     * @param int             $userId
     * @param string|null     $userSignature
     * @param string|null     $userIp
     * @param string|null     $userMac
     * @param int             $expires
     * @param int             $createTime
     * @param array           $permissions
     * @param array           $buf
     * 
     * @return static
     * 
     * @throws TokenDataBaseSecureException
     */
    static function factory(
        ITokenDataBase &$db,
        $dbId,
        string $hex,
        int $userId,
        ?string $userSignature,
        ?string $userIp,
        ?string $userMac,
        int $expires,
        int $createTime,
        array $permissions,
        array $buf
    );
    
    /** @return ITokenDataBase Возвращает базу данных токена */
    function &getDb() : ITokenDataBase;
    
    /** @return string|int|null Уникальный индекс токена в базе данных */
    function getDbId();
    
    /** @return string Уникальный хеш токена */
    function getHex() : string;
    
    /** @return int Уникальный номер пользователя */
    function getUserId() : int;
    
    /** @return string|null Хеш логина и пароля пользователя */
    function getUserSignature() : ?string;
    
    /** @return string|null IP адрес для которого формировался данный токен */
    function getUserIpOnRegistration(): ?string;
    
    /** @return string|null МАС адрес для которого формировался данный токен */
    function getUserMacOnRegistration(): ?string;
    
    /** @return int Время жизни токена в секундах */
    function getExpires() : int;
    
    /** @return int Время, когда токен был создан */
    function getCreateTime() : int;
    
    /** @return array Список полномочий, которыми наделяет данный токен */
    function getPermissions() : array;
    
    /**
     * Буфер обмена
     * @return array
     */
    function getBuffer() : array;
    
    /**
     * Проверяет, наделен ли данный токен указанными полномочиями
     * 
     * перед проверкой смотрят на системные полномочия
     * заданные в necessaryPermits
     * 
     * @param string $permit -> Индекс полномочия
     * @return bool
     */
    function checkPermissions(string ...$permit) : bool;
        
    /**
     * Список полномочий в виде строки
     * @return string
     */
    function getPermissionsStr() : string;
    
    /**
     * Проверка полномочий
     * 
     * @param string $name Код полномочия
     * @return bool
     */
    function hasPermission(string ...$name) : bool;
    
    /**
     * Время, когда истекет жизнь токена
     * 
     * 0 - бессмертный
     * 
     * @return int
     */
    function getTimeEnd() : int;
    
    /**
     * Токен истек
     * 
     * @return bool
     */
    function isExpired() : bool;
}