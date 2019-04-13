<?php

namespace LookPhp\Token;

use LookPhp\Crypt\RSA\PublicKey;
use LookPhp\Crypt\RSA\PrivateKey;

/**
 * Токен с возможностью открытия зашифрованного тунеля, для обмена данными
 */
interface ITunnelToken extends IToken
{
    /** @return PublicKey Публичный ключ шифрования клиента */
    function getEncryptUserKey() : PublicKey;
    
    /** @return PrivateKey Приватный ключ шифрования клиента */
    function getDecryptUserKey() : PrivateKey;
        
    /** @return PublicKey Публичный ключ шифрования */
    function getEncryptServerKey() : PublicKey;
    
    /** @return PrivateKey Приватный ключ шифрования */
    function getDecryptServerKey() : PrivateKey;
}