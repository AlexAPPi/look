<?php

namespace Look\API\Type\Token\DataBase;

use Look\API\Type\Token\IToken;
use Look\API\Type\Token\Struct\TokenContainer;

use Look\Crypt\RSA\PublicKey;
use Look\Crypt\RSA\PrivateKey;
use Look\Client\IP\Detector as IPDetector;

use Look\API\Type\Token\Exceptions\BadTokenException;
use Look\API\Type\Token\DataBase\Exceptions\TokenDataBaseException;
use Look\API\Type\Token\DataBase\Exceptions\TokenDataBaseSecureException;

use Look\Exceptions\InvalidArgumentException;

/**
 * Базовая обертка класса
 */
abstract class TokenDataBase implements ITokenDataBase
{    
    /**
     * Регистрирует новый токен в базе данных
     * 
     * @param int         $userId        -> ID пользователя
     * @param string|null $userSignature -> Хеш логина и пароля пользователя
     * @param string|null $userIp        -> IP адрес пользователя
     * @param string|null $userMac       -> МАС адрес пользователя
     * @param int         $expires       -> Время жизни токена в секундах
     * @param int         $createTime    -> Когда был создан токен
     * @param array       $permissions   -> Полномочия
     * @param array       $buf           -> Буфер обмена
     * @param string      $typeOf        -> Тип токена (должен быть наследник IToken)
     * 
     * @return IToken
     * 
     * @throws BadTokenException
     * @throws TokenDataBaseException
     * @throws TokenDataBaseSecureException
     */
    abstract protected function insert(
        int     $userId,
        ?string $userSignature,
        ?string $userIp,
        ?string $userMac,
        int     $expires,
        int     $createTime,
        array   $permissions,
        array   $buf,
        string $typeOf
    ) : IToken;
    
    /**
     * Вести лог использования логина
     * @return bool
     */
    abstract public function useAuthLog() : bool;
    
    /**
     * Регистрирует лог использования
     * 
     * @param string|int  $dbId          -> Уникальный ID в базе данных
     * @param int         $time          -> Время регистрации
     * @param int         $userId        -> ID пользователя
     * @param string|null $userSignature -> Хеш логина и пароля пользователя
     * @param string|null $userIp        -> IP адрес пользователя
     * @param string|null $userMac       -> МАС адрес пользователя
     * @param array       $data          -> Массив с данными, которые будут прикреплены к логу
     * @param bool        $isExpires     -> Токен устарел
     * @param bool        $check         -> Токен укомплектован
     * 
     * @throws BadTokenException
     * @throws TokenDataBaseException
     * @throws TokenDataBaseSecureException
     */
    abstract protected function insertLog(
        $dbId,
        int     $time,
        int     $userId,
        ?string $userSignature,
        ?string $userIp,
        ?string $userMac,
        array   $data,
        bool    $isExpires,
        bool    $check
    ) : void;
    
    /**
     * Извлекает данные токена из базы данных
     * 
     * @param string $tokenHex -> Публичный хеш токена
     * @param string $typeOf   -> Тип токена (должен быть наследник IToken)
     * 
     * @return IToken
     * 
     * @throws TokenDataBaseException
     * @throws TokenDataBaseSecureException
     */
    abstract protected function extract(string $tokenHex, string $typeOf = TokenContainer::class) : IToken;
    
    /** {@inheritdoc} */
    public function add(int $userId, string $userSignature, int $expires = 0, array $permissions = [], array $buf = [], string $typeOf = TokenContainer::class) : IToken
    {
        $userIp     = (string)(new IPDetector())->get();
        $userMac    = null; // TODO
        $createTime = time();
        
        if(!is_subclass_of($typeOf, IToken::class)) {
            throw new InvalidArgumentException("typeOf[$typeOf] не является наследником IToken");
        }
                
        return $this->insert(
            $userId,
            $userSignature,
            $userIp,
            $userMac,
            $expires,
            $createTime,
            $permissions,
            $buf,
            $typeOf
        );
    }
    
    /** {@inheritdoc} */
    public function get(string $tokenHex, string $typeOf = TokenContainer::class) : IToken
    {
        $userIp  = (string)(new IPDetector())->get();
        $userMac = null; // TODO
        
        if(!is_subclass_of($typeOf, IToken::class)) {
            throw new InvalidArgumentException("typeOf[$typeOf] не является наследником IToken");
        }
        
        $token = $this->extract($tokenHex, $typeOf);
        
        if($this->useAuthLog()) {
            
            $logData = [
                'url' => (string)\Look\Url\Currect::getInstance()
            ];
            
            $this->insertLog(
                $token->getDbId(),
                time(),
                $token->getUserId(),
                $token->getUserSignature(),
                $userIp,
                $userMac,
                $logData,
                $token->isExpired(),
                $token->checkPermissions()
            );
        }
        
        return $token;
    }
    
    /**
     * Генерация уникального индекса
     * @param int $lenght
     * @return string
     */
    protected function genUniqueId(int $lenght) : string
    {
        $bytes = random_bytes(ceil($lenght / 2));
        return substr(bin2hex($bytes), 0, $lenght);
    }
}