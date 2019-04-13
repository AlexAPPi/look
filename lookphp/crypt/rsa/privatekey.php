<?php

namespace LookPhp\Crypt\RSA;

use LookPhp\Crypt\RSA\PublicKey;

/**
 * Public Key
 */
class PrivateKey implements \JsonSerializable
{    
    const Delim = '|/|';
    
    /**
     * @var string
     */
    private $passphrase = '';
    
    /** @var resource */
    private $pk;
    
    /** @var resource|string */
    protected $certificate;
    
    /**
     * @param mixed $key <p><code>key</code> can be one of the following:</p>
     * <ol>
     *  <li>a string having the format file://path/to/file.pem. The named file must contain a PEM encoded certificate/private key (it may contain both).  </li>
     *  <li>A PEM formatted private key.</li>
     * </ol>
     * @param string $passphrase <p>The optional parameter <code>passphrase</code> must be used if the specified key is encrypted (protected by a passphrase).</p>
     * @return resource
     * @link http://php.net/manual/en/function.openssl-get-privatekey.php
     * @since PHP 4 >= 4.0.4, PHP 5, PHP 7
     */
    public function __construct($key, string $passphrase = '')
    {
        $tmp = explode(static::Delim, $key);
        
        if(count($tmp) == 2) {
            $key        = $tmp[0];
            $passphrase = $tmp[1];
        }
        
        $this->certificate = $key;
        $this->passphrase  = $passphrase;
        $this->initPk();
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        if(is_resource($this->pk)) {
            openssl_free_key($this->pk);
        }
    }
    
    /**
     * Convert to Public Key
     *
     * @return PublicKey
     */
    public function toPublicKey() : ?PublicKey
    {
        $details = openssl_pkey_get_details($this->pk);
        if($details && isset($details['key'])) {
            return new PublicKey($details['key']);
        }
        return null;
    }

    /**
     * @return array
     */
    public function __sleep() : array
    {
        return ['certificate', 'passphrase'];
    }
    
    /**
     * @return void
     */
    public function __wakeup() : void
    {
        $this->initPk();
    }
    
    /**
     * Init pk
     * @return void
     */
    protected function initPk() : void
    {
        $this->pk = openssl_get_privatekey($this->certificate, $this->passphrase);
    }
    
    /**
     * <p>The optional parameter <code>passphrase</code> must be used if the specified key is encrypted (protected by a passphrase).</p>
     * @return string
     */
    public function getPassPhrase() : string
    {
        return $this->passphrase;
    }
    
    /**
     * To string
     * @return string
     */
    public function __toString() : string
    {
        return $this->certificate . static::Delim . $this->passphrase;
    }
    
    /** {@inheritdoc} */
    public function jsonSerialize() : string
    {
        return (string)$this;
    }
    
    /**
     * Decrypt data
     * @param string $data    Data
     * @param int    $padding Padding
     * @return string|null string or null on error
     */
    public function decrypt(string $data, int $padding = OPENSSL_PKCS1_PADDING) : ?string
    {
        $decrypted = null;
        if(openssl_private_decrypt($data, $decrypted, $this->pk, $padding)) {
            return $decrypted;
        }
        return null;
    }
    
    /**
     * Convert to hex
     * @return string
     */
    public function toHex() : string
    {
        $string = (string)$this;
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $ord     = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex    .= substr('0'.$hexCode, -2);
        }
        return $hex;
    }
    
    /**
     * Convert from hex
     * @param string $hex  Hex
     * @param IMath  $math Math class
     * @return PrivateKey
     */
    public static function fromHex(string $hex) : PrivateKey
    {
        $string='';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2){
            $string .= chr(hexdec($hex[$i].$hex[$i + 1]));
        }
        return new static($string);
    }
}