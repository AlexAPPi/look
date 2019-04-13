<?php

namespace LookPhp\Crypt\RSA;

/**
 * Public Key
 */
class PublicKey implements \JsonSerializable
{    
    /** @var resource */
    private $pk;
    
    /** @var resource|string */
    protected $certificate;
        
    /**
     * @param mixed $certificate <p><code>certificate</code> can be one of the following:</p>
     * <ol>
     *  <li>an X.509 certificate resource</li>
     *  <li>a string having the format file://path/to/file.pem. The named file must contain a PEM encoded certificate/public key (it may contain both).  </li>
     *  <li>A PEM formatted public key.</li>
     * </ol>
     * @return resource
     * @link http://php.net/manual/en/function.openssl-get-publickey.php
     * @since PHP 4 >= 4.0.4, PHP 5, PHP 7
     */
    public function __construct(string $certificate)
    {
        $this->certificate = $certificate;
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
     * Init pk
     * @return void
     */
    protected function initPk() : void
    {
        $this->pk = openssl_get_publickey($this->certificate);
    }
    
    /**
     * @return array
     */
    public function __sleep() : array
    {
        return ['certificate'];
    }
    
    /**
     * @return void
     */
    public function __wakeup() : void
    {
        $this->initPk();
    }
    
    /**
     * Encrypt data
     * @param string $data    Data
     * @param int    $padding Padding
     * @return string|null string or null on error
     */
    public function encrypt(string $data, int $padding = OPENSSL_PKCS1_PADDING) : ?string
    {
        $encrypted = null;
        if(openssl_public_encrypt($data, $encrypted, $this->pk, $padding)) {
            return $encrypted;
        }
        return null;
    }
    
    /**
     * To string
     * @return string
     */
    public function __toString() : string
    {
        return $this->certificate;
    }
    
    /** {@inheritdoc} */
    public function jsonSerialize() : string
    {
        return (string)$this;
    }
    
    /**
     * Convert to hex
     * @return string
     */
    public function toHex() : string
    {
        $string = $this->certificate;
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
     * @return PublicKey
     */
    public static function fromHex(string $hex) : PublicKey
    {
        $string='';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2){
            $string .= chr(hexdec($hex[$i].$hex[$i + 1]));
        }
        return new static($string);
    }
}