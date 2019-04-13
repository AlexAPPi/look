<?php

namespace LookPhp\Token\DB;

use LookPhp\Token\IToken;
use LookPhp\Token\Container\TokenContainer;
use LookPhp\Token\Exceptions\BadTokenException;
use LookPhp\Token\DB\Exceptions\TokenDataBaseException;
use LookPhp\Token\DB\Exceptions\TokenDataBaseSecureException;

use LookPhp\Exceptions\InvalidArgumentException;

interface ITokenDataBase
{
    /**
     * Возращает экземпляр класса
     * @return static
     */
    public static function getInstance();
    
    /**
     * Деактивирует все ключи доступа для данного пользователя
     * 
     * @param int $userId -> Уникальный индекс пользователя
     * @return int Количесто деактивированных ключей
     */
    public function removeAccess(int $userId) : int;
    
    /**
     * Активирует доступ к токену
     * 
     * @param string $tokenHex -> Публичный кеш токена
     * @return bool
     */
    public function accessFor(string $tokenHex) : bool;
    
    /**
     * Деактивирует доступ для токена
     * 
     * @param string $tokenHex -> Публичный кеш токена
     * @return bool
     */
    public function removeAccessFor(string $tokenHex) : bool;
    
    /**
     * Добавляет токен в базу данных
     *
     * @param int    $userId        -> Уникальный индекс пользователя
     * @param string $userSignature -> Хеш логина и пароля
     * @param int    $expires       -> Время жизни токена в секундах
     * @param array  $permissions   -> Список полномочий, которыми наделяет данный токен
     * @param array  $buf           -> Буфер обмена
     * @param string $typeOf        -> Тип токена (должен быть наследник IToken)
     * @return IToken
     * @throws InvalidArgumentException
     * @throws BadTokenException
     * @throws TokenDataBaseException
     * @throws TokenDataBaseSecureException
     */
    public function add(int $userId, string $userSignature, int $expires = 0, array $permissions = [], array $buf = [], string $typeOf = TokenContainer::class) : IToken;
    
    /**
     * Извлекает данные токена из базы данных
     * 
     * @param string $tokenHex -> Публичный кеш токена
     * @param string $typeOf   -> Тип токена (должен быть наследник IToken)
     * @return IToken
     * @throws InvalidArgumentException
     * @throws BadTokenException
     * @throws TokenDataBaseException
     * @throws TokenDataBaseSecureException
     */
    public function get(string $tokenHex, string $typeOf = TokenContainer::class) : IToken;
}