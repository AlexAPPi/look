<?php

namespace LookPhp\Client\IP;

/**
 * Version: 0.0.1
 * Updated: 2015.08.12
 * Author:  Dariusz Prząda (artdarek@gmail.com)
 */
class Detector implements IDetector
{
    /**
     * [$data description] 
     * [getenv/server]
     * @var [type]
     */
    private $method = 'getenv';
    
    /**
     * [$default description]
     * @var string
     */
    private $default = null;
    
    /**
     * [$methods description]
     * @var [type]
     */
    private $methods = [
        'server',
        'getenv'
    ];
    
    /**
     * [$keys description]
     * @var [type]
     */
    private $keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    /**
     * [$ip description]
     * @var [type]
     */
    private $ip;
    
    /**
     * Class constructor
     * @return void
     */
    public function __construct($method = null) 
    {
        if ($method != null) {
            $this->setMethod($method);
        }
    }
    
    /**
     * Set method
     * @param  string $method
     * @return RealIP
     */
    public function setMethod($method)  
    {	
        if (in_array($method, $this->methods)) {
            $this->method = $method;
        }
        return $this;
    }
    
    /**
     * Set default 
     * @param  string $default
     * @return $this
     */
    public function setDefault($default)  
    {	
        $this->default = $default;
        return $this;
    }
    
    /**
     * Detect IP
     * @return $this
     */
    public function detect() : bool
    {
        $ip = $this->default;
        foreach($this->keys as $key) {
            if ($this->_check($key)) {
                $ip = $this->_check($key);
            } else {
                continue; 
            }
        }
        $this->ip = $ip;
        return true;
    }
    
    /**
     * Load config
     * @return string
     */
    private function _check($key) 
    {
        $response = false;
        switch ($this->method) {
            default:
            case 'getenv':
                if(getenv($key)) {
                    $response = getenv($key);
                }
            break;
            case 'server':
                if(!empty($_SERVER[$key])) {
                    $response = $_SERVER[$key];
                }
            break;
        }

        return $response;
    }
        
    /**
     * Get item from config
     * @param  string $key
     * @return mixed
     */
    public function get()
    {
        $this->detect();
        return $this->ip;
    }
}