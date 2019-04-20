<?php

namespace Look\Crypt\RSA;

use JsonSerializable;
use Look\API\APIResultable;

use Look\Crypt\RSA\PublicKey;
use Look\Crypt\RSA\PrivateKey;

use Look\Crypt\RSA\Exceptions\RSAExcepton;

/**
 * Структура защищенный данных
 */
class ProtectedStringData implements JsonSerializable, APIResultable
{
    protected $data;
    protected $encrypt;
    protected $decrypt;
    
    /**
     * ProtectedData
     * @param string $data Данные, которые нужно зашифровать или расшифровать
     */
    public function __construct(string $data)
    {
        $this->data = $data;
    }
    
    /**
     * @return array
     */
    public function __sleep() : array
    {
        return ['data'];
    }
    
    /**
     * @return void
     */
    public function __wakeup() : void
    {
    }
    
    /**
     * Данные зашифрованны
     * @return bool
     */
    public function isEncrypted() : bool
    {
        return $this->encrypt !== null;
    }
    
    /**
     * Данные расшифрованны
     * @return bool
     */
    public function isDecrypted() : bool
    {
        return $this->decrypt !== null;
    }
    
    /**
     * Encrypt data
     * @param PublicKey $key     PublicKey
     * @param int       $padding Padding
     * @return static
     */
    public function encryptBy(PublicKey $key, int $padding = OPENSSL_PKCS1_PADDING)
    {
        if(!$this->isEncrypted()) {
            $tmp           = $key->encrypt($this->data, $padding);
            $this->encrypt = bin2hex($tmp);
        }
        
        return $this;
    }
    
    /**
     * Decrypt data
     * @param PrivateKey $key     PrivateKey
     * @param int        $padding Padding
     * @return static
     */
    public function decryptBy(PrivateKey &$key, int $padding = OPENSSL_PKCS1_PADDING)
    {
        if(!$this->isDecrypted()) {
            $decode        = hex2bin($this->data);
            $this->decrypt = $key->decrypt($decode, $padding);
        }
        
        return $this;
    }
    
    /** {@inheritdoc} */
    public function getValue()
    {
        return $this->data;
    }
    
    /** {@inheritdoc} */
    public function setValue($value): void
    {
        $this->data = $value;
        $this->encrypt = null;
        $this->decrypt = null;
    }
    
    /**
     * Возвращает зашифрованные данные
     * @return string|null
     */
    public function getEncrypt() : ?string
    {
        return $this->encrypt;
    }
    
    /**
     * Возвращает расшифрованные данные
     * @return string|null
     */
    public function getDecrypt() : ?string
    {
        return $this->decrypt;
    }
    
    /**
     * @return string
     */
    public function __toString() : string
    {
        if($this->encrypt === null) {
            throw new RSAExcepton('данные не зашифрованны публичным ключом');
        }
        
        return $this->encrypt;
    }
    
    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return (string)$this;
    }
    
    /**
     * @return string
     */
    public function toAPIResult()
    {
        return $this->jsonSerialize();
    }
}