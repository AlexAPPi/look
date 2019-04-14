<?php

namespace Look\Token\Container;

use Look\Token\ITunnelToken;
use Look\Token\DB\ITokenDataBase;

use Look\Crypt\RSA\KeyPair;
use Look\Crypt\RSA\PublicKey;
use Look\Crypt\RSA\PrivateKey;

use Look\Token\Exceptions\BadTokenException;

/**
 * Токен с тунелем шифрования трафика
 */
class TunnelTokenContainer extends TokenContainer implements ITunnelToken
{
    /**
     * @var int Размер пары ключей
     */
    const keyPairSize = 1024;
    
    /** {@inheritdoc} */
    public function &getEncryptUserKey() : PublicKey
    {
        return $this->buf['__tunnelEncryptUser'];
    }
    
    /** {@inheritdoc} */
    public function &getDecryptUserKey() : PrivateKey
    {
        return $this->buf['__tunnelDecryptUser'];
    }
        
    /** {@inheritdoc} */
    public function &getEncryptServerKey() : PublicKey
    {
        return $this->buf['__tunnelEncryptServer'];
    }
    
    /** {@inheritdoc} */
    public function &getDecryptServerKey() : PrivateKey
    {
        return $this->buf['__tunnelDecryptServer'];
    }
        
    /** {@inheritdoc} */
    protected static function beforeCreate(ITokenDataBase &$db, int &$userId, string &$userSignature, int &$expires, array &$buf, array &$permits) : void
    {
        $userKeyPair   = new KeyPair(static::keyPairSize);        
        $serverKeyPair = new KeyPair(static::keyPairSize);
        
        $buf['__tunnelEncryptServer'] = $userKeyPair->getPublicKey();
        $buf['__tunnelDecryptUser']   = $userKeyPair->getPrivateKey();
        $buf['__tunnelEncryptUser']   = $serverKeyPair->getPublicKey();
        $buf['__tunnelDecryptServer'] = $serverKeyPair->getPrivateKey();
    }
    
    /** {@inheritdoc} */
    protected static function afterCreate(&$token) : void
    {
        // Данный токен не является тунельным
        $buf = $token->getBuffer();
        if(!isset($buf['__tunnelDecryptServer'])) {
            throw new BadTokenException();
        }
    }
    
    /** {@inheritdoc} */
    public function jsonSerialize() : array
    {
        return [
            'access_token' => $this->getHex(),
            'public_key'   => $this->getEncryptUserKey()->toHex(),
            'private_key'  => $this->getDecryptUserKey()->toHex(),
            'expires_in'   => $this->getTimeEnd(),
        ];
    }
}