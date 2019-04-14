<?php

namespace Look\Token\DB;

use Look\Token\IToken;
use Look\Token\TokenContainer;

use Look\Token\DB\TokenDataBase;
use Look\Token\DB\Exceptions\FSTokenDataBaseException;
use Look\Token\DB\Exceptions\FSTokenDataBaseSecureException;

use Look\Token\Exceptions\BadTokenException;

/**
 * Базовая файловая база данных токенов
 */
class FSTokenDataBase extends TokenDataBase
{
    /** @var string Количесто символов в имени 1 ветки */
    const BrancheNameLen = 2;
    
    /** @var string Количество ветвей до файла */
    const BranchesCount = 4;
    
    /** @var string Расширение файлов */
    const TokenExpansion = 'token';
    
    use \Look\Type\Traits\Singleton;
    use \Look\Type\Traits\Settingable;
    
    private $algo;
    private $folder;
    private $folderMode;
    
    /** @var bool Вести лог авторизации по токену */
    private $useAuthLog;
    
    /**
     * Конструктор базы данных токенов
     * @throws TokenDataBaseException -> Исключение возникающее при ошибке подключения к базе данных
     */
    private function __construct()
    {
        $this->folder     = $this->getSetting('folder', BASE_DIR . DIRECTORY_SEPARATOR . '~tokendb');
        $this->folderMode = $this->getSetting('folder_mode', 0777);
        $this->useAuthLog = $this->getSetting('useauthlog', true);
        $this->algo       = $this->getSetting('algo', 'sha256');
        
        if(!file_exists($this->folder)) {
            mkdir($this->folder, $this->folderMode);
        }
    }
    
    /** {@inheritdoc} */
    public function useAuthLog(): bool
    {
        return $this->useAuthLog;
    }
        
    /**
     * Формирует цепочку из частей токена
     * @param string $tokenHex
     * @return array
     * @throws TokenDataBaseException
     */
    protected function getBranches(string $tokenHex) : array
    {
        if(empty($tokenHex)) {
            throw new FSTokenDataBaseException('токен пустой инициализация не возможна');
        }
        
        // Проверка на запрещенные символы
        if(strpos($tokenHex, '/')  !== false
        || strpos($tokenHex, '\\') !== false
        || strpos($tokenHex, '.')  !== false
        || strpos($tokenHex, ':')  !== false) {
            throw new FSTokenDataBaseSecureException('token содержит запрещенные символы');
        }
        
        $len = static::BranchesCount * static::BrancheNameLen;
        $res = [];
        
        for($i = 0; $i < $len; $i += static::BrancheNameLen) {
            $res[] = substr($tokenHex, $i, static::BrancheNameLen);
        }
        
        $count = count($res);
        
        if($count == 0) {
            throw new FSTokenDataBaseException('токен пустой инициализация не возможна');
        }
        
        if(strlen($res[$count - 1]) == 0) {
            array_pop($res);
        }
        
        return $res;
    }
    
    /**
     * Формирует полный путь к папке токена
     * 
     * @param string $tokenHex -> Токен
     * @param bool   $make     -> Создает путь
     * @return string
     * 
     * @throws TokenDataBaseException
     */
    protected function buildPathOfFolders(string $tokenHex, bool $make = false) : string
    {
        $SEP      = DIRECTORY_SEPARATOR;
        $branches = $this->getBranches($tokenHex);
        $path     = $this->folder;
        
        foreach($branches as $branche) {
            $path .= $SEP . $branche;
            if($make && !file_exists($path)) {
                if(!mkdir($path, $this->folderMode)) {
                    throw new FSTokenDataBaseException("не удалось сформировать путь: $path");
                }
            }
        }
        
        return $path;
    }
    
    /**
     * Формирует полный путь к файлу токена
     * @param string $tokenHex -> Токен
     * @param bool   $make     -> Создает путь
     * @return string
     * 
     * @throws TokenDataBaseException
     */
    protected function buildPath(string $tokenHex, bool $make = false) : string
    {
        $SEP  = DIRECTORY_SEPARATOR;
        $path = $this->buildPathOfFolders($tokenHex, $make);
        $exp  = static::TokenExpansion;
        return "$path$SEP$tokenHex.$exp";
    }
    
    /** {@inheritdoc} */
    public function removeAccess(int $userId) : int
    {
        throw new FSTokenDataBaseException('метод не поддерживается');
        return 0;
    }
    
    /** {@inheritdoc} */
    public function accessFor(string $tokenHex) : bool
    {
        $file = $this->buildPath($tokenHex, false);
        if(file_exists($file)) {
            if(rename($file.'.unable', $file)) {
                return true;
            }
        }
        // Возвращаем ошибку связанную
        // с неверным токеном
        return false;
    }
    
    /** {@inheritdoc} */
    public function removeAccessFor(string $tokenHex) : bool
    {
        $file = $this->buildPath($tokenHex, false);
        if(file_exists($file)) {
            if(rename($file, $file.'.unable')) {
                return true;
            }
        }
        // Возвращаем ошибку связанную
        // с неверным токеном
        return false;
    }
    
    /**
     * Возращает данные токена
     * @param string $tokenHex
     * @return object|null
     * @throws BadTokenException
     */
    protected function getTokenDataByHex(string $tokenHex) : ?object
    {
        $file = $this->buildPath($tokenHex, false);
        if(file_exists($file)) {
            $data = json_decode(file_get_contents($file));
            if($data) {
                return $data;
            }
        }
        // Возвращаем ошибку связанную
        // с неверным токеном
        throw new BadTokenException();
    }
    
    /** {@inheritdoc} */
    protected function extract(string $tokenHex, string $typeOf = TokenContainer::class): IToken
    {
        if(!empty($tokenHex) && strlen($tokenHex) > 7) {
            $data = $this->getTokenDataByHex($tokenHex);
            if($data != null) {
                
                $factory = "$typeOf::factory";                
                return $factory(
                    $this,
                    $tokenHex,
                    $tokenHex,
                    $data->userId,
                    $data->userSignature,
                    $data->userIp,
                    $data->userMac,
                    $data->expires,
                    $data->createTime,
                    unserialize($data->permissions),
                    unserialize($data->buf)
                );
            }
        }
        
        // Возвращаем ошибку связанную
        // с неверным токеном
        throw new BadTokenException();
    }
    
    /** {@inheritdoc} */
    protected function insert(
        int        $userId,
        ?string    $userSignature,
        ?string    $userIp,
        ?string    $userMac,
        int        $expires,
        int        $createTime,
        array      $permissions,
        array      $buf,
        string     $typeOf
    ) : IToken {
                
        $strForHex = $userId . $userIp . $userMac . $userSignature . static::class . $this->genUniqueId(12);
        $tokenHex  = hash($this->algo, $strForHex);
        
        if(empty($tokenHex) || strlen($strForHex) < 8) {
            throw new FSTokenDataBaseException('не удалось создать токен');
        }
        
        $data = [
            'userId'        => $userId,
            'userSignature' => $userSignature,
            'userIp'        => $userIp,
            'userMac'       => $userMac,
            'expires'       => $expires,
            'createTime'    => $createTime,
            'permissions'   => serialize($permissions),
            'buf'           => serialize($buf)
        ];
        
        $tokenFormat = json_encode($data);
        $tokenFile   = $this->buildPath($tokenHex, true);
        
        if(json_last_error() === JSON_ERROR_NONE
        && file_put_contents($tokenFile, $tokenFormat) !== false) {
            
            $factory = "$typeOf::factory";
            return $factory(
                $this,
                $tokenHex,
                $tokenHex,
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
        
        throw new FSTokenDataBaseException('не удалось создать токен');
    }
    
    /** {@inheritdoc} */
    protected function insertLog(
        $dbId,
        int     $time,
        int     $userId,
        ?string $userSignature,
        ?string $userIp,
        ?string $userMac,
        array   $data,
        bool    $isExpires,
        bool    $check
    ) : void {
        
        $tokenFolder    = $this->buildPathOfFolders($dbId, false);
        $tokenLogFolder = $tokenFolder . DIRECTORY_SEPARATOR . 'log';
        
        if(!file_exists($tokenLogFolder) && !mkdir($tokenLogFolder, $this->folderMode)) {
            throw new FSTokenDataBaseException('не удалось создать папку с логами');
        }
        
        $log = [
            'dbId'          => $dbId,
            'time'          => $time,
            'userId'        => $userId,
            'userSignature' => $userSignature,
            'userIp'        => $userIp,
            'userMac'       => $userMac,
            'data'          => serialize($data),
            'isExpires'     => $isExpires,
            'check'         => $check
        ];
        
        $logStr = json_encode($log) . PHP_EOL;
        
        // .../log/1994_12_05_21_60.txt
        $logFile = $tokenLogFolder . DIRECTORY_SEPARATOR . date('Y_m_d_H_i') . '.txt';
        
        if(json_last_error() !== JSON_ERROR_NONE
        || file_put_contents($logFile, $logStr, FILE_APPEND | LOCK_EX) === false) {
            throw new FSTokenDataBaseException('не удалось запись лог');
        }
    }
}