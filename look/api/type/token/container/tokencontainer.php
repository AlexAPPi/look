<?php

namespace Look\API\Type\Token\Container;

use JsonSerializable;

use Look\API\Caller;

use Look\API\Type\Interfaces\APIResultable;

use Look\API\Type\Token\IToken;
use Look\API\Type\Token\Exceptions\TokenException;

use Look\API\Type\Token\DataBase\ITokenDataBase;
use Look\API\Type\Token\DataBase\Exceptions\TokenDataBaseSecureException;

/**
 * Структура данных токена в базе данных
 */
class TokenContainer implements IToken, JsonSerializable, APIResultable
{
    /** @var array Массив с необходимыми правами */
    const necessaryPermits = [];
    
    /** @var ITokenDataBase база данных, в которой хранится токен */
    protected $db;

    /** @var string Уникальный индекс токена в базе данных */
    protected $dbId;

    /** @var string Уникальный хеш токена */
    protected $hex;
    
    /** @var int Уникальный номер пользователя */
    protected $userId;
    
    /** @var string Хеш логина и пароля пользователя */
    protected $userSignature;
    
    /** @var string|null IP адрес для которого формировался токен */
    protected $userIp;
    
    /** @var string|null MAC адрес для которого формировался токен */
    protected $userMac;
    
    /** @var int Время жизни токена в секундах */
    protected $expires;
    
    /** @var int Время, когда токен был создан */
    protected $createTime;
    
    /** @var array Массив с правами */
    protected $permissions = [];
    
    /** @var array Массив с буфером обмена */
    protected $buf = [];
    
    /**
     * Возвращает список полномочий
     * @return array
     */
    final public static function getPermits() : array
    {
        $permits = static::necessaryPermits;
        $parent  = get_parent_class(static::class);
        if($parent !== false && $parent != self::class) {
            $func   = "$parent::getPermits";
            $values = $func();
            if(is_array($values)) {
                $permits = array_merge($permits, $values);
            } else {
                throw new TokenException("Класс [$parent] токена должен содержать константу necessaryPermits с наделяемыми полномочиями");
            }
        }
        return array_unique($permits);
    }
    
    /**
     * Происходит перед добавлением токена
     * 
     * @param ITokenDataBase &$db
     * @param int            &$userId
     * @param string         &$userSignature
     * @param int            &$expires
     * @param array          &$buf
     * @param array          &$permits
     * 
     * @return void
     */
    protected static function beforeCreate(ITokenDataBase &$db, int &$userId, string &$userSignature, int &$expires, array &$buf, array &$permits) : void {}
    
    /**
     * Происходит после добавления токена
     * @param IToken $token
     * @return void
     */
    protected static function afterCreate(&$token) : void {}
    
    /**
     * Формирует токен доступа
     * 
     * @param int            $userId        -> ID пользователя
     * @param string         $userSignature -> Хеш логина и пароля
     * @param int            $expires       -> Время жизни в секундах, по умолчанию: 0 - бессмертный токен
     * @param array          $buf           -> Данные хранящаяся в буфере обмена
     * 
     * @see \Look\API\Caller::getTokenDB
     * 
     * @return static
     */
    final public static function create(int $userId, string $userSignature, int $expires = 0, array $buf = [])
    {
        $db      = &Caller::getTokenDB();
        $permits = static::getPermits();
        
        static::beforeCreate($db, $userId, $userSignature, $expires, $buf, $permits);
        $result = $db->add($userId, $userSignature, $expires, $permits, $buf, static::class);
        static::afterCreate($result);
        return $result;
    }
    
    /**
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
    final public static function factory(
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
    ) {
        return new static(
            $db,
            $dbId,
            $hex,
            $userId,
            $userSignature,
            $userIp,
            $userMac,
            $expires,
            $createTime,
            $permissions,
            $buf
        );
    }
    
    /**
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
     * @throws TokenDataBaseSecureException
     */
    final public function __construct(
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
    ) {
        
        if(strlen($hex) < 8) {
            throw new TokenDataBaseSecureException('Длина ключа токена, должна быть больше 8 символов');
        }
        
        $this->db            = &$db;
        $this->dbId          = $dbId;
        $this->hex           = $hex;
        $this->userId        = $userId;
        $this->userSignature = $userSignature;
        $this->userIp        = $userIp;
        $this->userMac       = $userMac;
        $this->expires       = $expires;
        $this->createTime    = $createTime;
        $this->buf           = $buf;
                
        if(count($permissions) > 0) {
            
            $this->addPermission(...$permissions);
        }
    }
    
    /** {@inheritdoc} */
    public function &getDb() : ITokenDataBase
    {
        return $this->db;
    }
    
    /** {@inheritdoc} */
    public function getDbId()
    {
        return $this->dbId;
    }
    
    /** {@inheritdoc} */
    public function getHex() : string
    {
        return $this->hex;
    }
    
    /** {@inheritdoc} */
    public function getUserId() : int
    {
        return $this->userId;
    }
    
    /** {@inheritdoc} */
    public function getUserSignature() : ?string
    {
        return $this->userSignature;
    }
    
    /** {@inheritdoc} */
    public function getUserIpOnRegistration(): ?string
    {
        return $this->userIp;
    }
    
    /** {@inheritdoc} */
    public function getUserMacOnRegistration(): ?string
    {
        return $this->userMac;
    }
    
    /** {@inheritdoc} */
    public function getExpires() : int
    {
        return $this->expires;
    }
    
    /** {@inheritdoc} */
    public function getCreateTime() : int
    {
        return $this->createTime;
    }
        
    /** {@inheritdoc} */
    public function getPermissions() : array
    {
        return $this->permissions;
    }
    
    /** {@inheritdoc} */
    public function getBuffer() : array
    {
        return $this->buf;
    }
    
    /** {@inheritdoc} */
    public function checkPermissions(string ...$permit) : bool
    {
        if($permit === null || count($permit) == 0) {
            $permit = static::necessaryPermits;
        }
        
        return $this->hasPermission(...$permit);
    }
        
    /** {@inheritdoc} */
    public function getPermissionsStr() : string
    {
        return implode(',', $this->permissions);
    }
    
    /**
     * Добавляет разрешение в токен
     * 
     * @param string $name
     * @return $this
     */
    protected function addPermission(string ...$name) : void
    {
        foreach($name as $item) {
            if(!in_array($item, $this->permissions)) {
                $this->permissions[] = strtolower($item);
            }
        }
    }
    
    /** {@inheritdoc} */
    public function hasPermission(string ...$name) : bool
    {
        foreach($name as $item) {
            if(!in_array(strtolower($item), $this->permissions)) {
                return false;
            }
        }
        
        return true;
    }
    
    /** {@inheritdoc} */
    public function getTimeEnd() : int
    {
        // бесконечный токен
        if($this->expires == 0) {
            return 0;
        }
        
        return $this->createTime + $this->expires;
    }
    
    /** {@inheritdoc} */
    public function isExpired() : bool
    {
        if($this->expires == 0) {
            return false;
        }
        
        return $this->getTimeEnd() < time();
    }
    
    /** {@inheritdoc} */
    public function jsonSerialize() : array
    {
        return [
            'access_token' => $this->getHex(),
            'expires_in'   => $this->getTimeEnd(),
        ];
    }
    
    /** {@inheritdoc} */
    public function toAPIResult() : array
    {
        return $this->jsonSerialize();
    }
}
