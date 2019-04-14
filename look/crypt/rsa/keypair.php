<?php

namespace Look\Crypt\RSA;

use Look\Crypt\RSA\PublicKey;
use Look\Crypt\RSA\PrivateKey;
use Look\Crypt\RSA\Exceptions\RSAExcepton;

/**
 * Key Pair class
 */
class KeyPair
{
    /**
     * @var PrivateKey
     */
    private $privateKey;
    
    /**
     * @var PublicKey
     */
    private $publicKey;
    
    /**
     * 
     * @param int         $size
     * @param string|null $passphrase
     * @param array $settings
     * @throws RSAExcepton
     */
    public function __construct(int $size, ?string $passphrase = null, ...$settings)
    {
        if(!is_array($settings)) {
            $settings = [];
        }

        $settings['private_key_bits'] = $size;
        $pkey = openssl_pkey_new($settings);

        $strPrivateKey  = null;
        if(!openssl_pkey_export($pkey, $strPrivateKey, $passphrase)) {
            throw new RSAExcepton('failed export private key');
        }
        $dataOpenSSLKey = openssl_pkey_get_details($pkey);

        $this->privateKey = new PrivateKey($strPrivateKey, $passphrase ?? '');
        $this->publicKey  = new PublicKey($dataOpenSSLKey['key']);
    }
    
    /**
     * Возвращает публичный ключ
     * @return PublicKey
     */
    public function getPublicKey() : PublicKey
    {
        return $this->publicKey;
    }
    
    /**
     * Возвращает приватный ключ
     * @return PrivateKey
     */
    public function getPrivateKey() : PrivateKey
    {
        return $this->privateKey;
    }
    
    /**
     * Encrypt data
     * @param string $data    Data
     * @param int    $padding Padding
     * @return string|null string or null on error
     */
    public function encrypt(string $data, int $padding = OPENSSL_PKCS1_PADDING) : ?string
    {
        return $this->publicKey->encrypt($data, $padding);
    }
    
    /**
     * Decrypt data
     * @param string $data    Data
     * @param int    $padding Padding
     * @return string|null string or null on error
     */
    public function decrypt(string $data, int $padding = OPENSSL_PKCS1_PADDING) : ?string
    {
        return $this->privateKey->decrypt($data, $padding);
    }
}