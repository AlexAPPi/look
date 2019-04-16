<?php

namespace Look\API\Type\Token\DataBase;

use mysqli;

use Look\API\Type\Token\IToken;
use Look\API\Type\Token\Container\TokenContainer;

use Look\API\Type\Token\DataBase\TokenDataBase;
use Look\API\Type\Token\DataBase\Exceptions\MySQLiTokenDataBaseException;
use Look\API\Type\Token\DataBase\Exceptions\MySQLiTokenDataBaseSecureException;

use Look\API\Type\Token\Exceptions\BadTokenException;

/**
 * Базовая файловая база данных токенов
 */
class MySQLiTokenDataBase extends TokenDataBase
{    
    use \Look\Type\Traits\Singleton;
    use \Look\Type\Traits\Settingable;
    
    /** @var bool Вести лог авторизации по токену */
    private $useAuthLog;
    
    private $algo;
    private $mysqli;
    private $debugMode;
    private $dbInit;
    private $dbHost;
    private $dbUser;
    private $dbPassword;
    private $dbName;
    private $dbPort;
    private $dbTable;
    private $dbAuthLogTable;
            
    /**
     * Конструктор базы данных токенов
     * @throws MySQLiTokenDataBaseException
     * @throws MySQLiTokenDataBaseSecureException
     */
    private function __construct()
    {
        $this->debugMode      = $this->getSetting('debug', false);
        $this->dbHost         = $this->getSetting('host', 'localhost');
        $this->dbUser         = $this->getSetting('user', 'root');
        $this->dbPassword     = $this->getSetting('password');
        $this->dbName         = $this->getSetting('name');
        $this->dbPort         = $this->getSetting('port', 3306);
        $this->useAuthLog     = $this->getSetting('useauthlog', true);
        $this->dbTable        = $this->getSetting('table', 'token');
        $this->dbAuthLogTable = $this->getSetting('authlogtable', 'token_auth_log');
        $this->algo           = $this->getSetting('algo', 'sha256');
        $this->dbInit         = $this->getSetting('init', false);
        $this->mysqli         = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName, $this->dbPort);
        
        if ($this->mysqli->connect_error) {
            throw new MySQLiTokenDataBaseException('Ошибка подключения к базе данных');
        }
        
        if(empty(trim($this->dbTable))) {
            throw new MySQLiTokenDataBaseException('Не задано название для таблицы хранящей токены');
        }
        if(empty(trim($this->dbAuthLogTable))) {
            throw new MySQLiTokenDataBaseException('Не задано название для таблицы хранящей лог для токенов');
        }
        
        // Проверка имени таблиц на безопасность
        if(!preg_match('/^[A-Za-z_]*$/', $this->dbTable)) {
            throw new MySQLiTokenDataBaseSecureException('В названии таблицы обнаружен sql inject');
        }
        if(!preg_match('/^[A-Za-z_]*$/', $this->dbAuthLogTable)) {
            throw new MySQLiTokenDataBaseSecureException('В названии таблицы лога обнаружен sql inject');
        }
        
        $this->query("SET NAMES 'utf8';");
        
        // Инициализируем базу данных
        if($this->dbInit !== true) {
            $this->dbInit = $this->buildTables();
            $this->setSetting('init', $this->dbInit);
        }
        
        if($this->mysqli->errno != 0) {
            throw new MySQLiTokenDataBaseSecureException('Не удалось инициализировать работу базы данных токен системы, возникла ошибка: ' . $this->mysqli->error);
        }
    }
    
    /** {@inheritdoc} */
    public function useAuthLog(): bool
    {
        return $this->useAuthLog;
    }
    
    /**
     * Деструктор файла
     */
    public function __destruct()
    {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }

    /**
     * Создает таблицы которые нужны для работы класса
     */
    private function buildTables() : bool
    {
        $fixTable1 = $this->quoteName($this->dbTable);
        $fixTable2 = $this->quoteName($this->dbAuthLogTable);
        $querys = "
            SET NAMES 'utf8';
            CREATE TABLE IF NOT EXISTS $fixTable1 (
                `id`             int(11) NOT NULL AUTO_INCREMENT,
                `token_hex`      varchar(64) NOT NULL,
                `user_id`        int(11) NOT NULL,
                `user_signature` varchar(64) DEFAULT NULL,
                `user_ip`        varchar(16) DEFAULT NULL,
                `user_mac`       varchar(18) DEFAULT NULL,
                `expires`        int(11) DEFAULT NULL,
                `create_time`    int(11) DEFAULT NULL,
                `permissions`    text DEFAULT NULL,
                `buffer`         text DEFAULT NULL,
                `active`         tinyint(1) DEFAULT 1,
                PRIMARY KEY(`id`),
                unique(`token_hex`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            CREATE TABLE IF NOT EXISTS $fixTable2 (
                `id`             int(11) NOT NULL AUTO_INCREMENT,
                `token_id`       int(11) NOT NULL,
                `user_id`        int(11) NULL,
                `user_signature` varchar(64) DEFAULT NULL,
                `user_ip`        varchar(16) DEFAULT NULL,
                `user_mac`       varchar(18) DEFAULT NULL,
                `is_expires`     tinyint(1) DEFAULT 0,
                `check`          tinyint(1) DEFAULT 0,
                `data`           text DEFAULT NULL,
                `time`           int(11) NOT NULL,        
                PRIMARY KEY(`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        if(mysqli_multi_query($this->mysqli, $querys)) {
            do { } while (
                mysqli_more_results($this->mysqli) &&
                mysqli_next_result($this->mysqli)
            );
            if($this->mysqli->errno === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Экранирует кавычки для части запроса.
     * 
     * @param srting $string  -> часть запроса.
     * @param bool   $noQuote -> если true то не будет выводить кавычки вокруг строки.
     * @return string
     */
    private function quote(string $string, bool $noQuote = false) : string
    {
        $value = mysqli_real_escape_string($this->mysqli, $string);
        return !$noQuote ? '"' . $value . '"' : $value;
    }
    
    /**
     * Экранирует кавычки для части запроса.
     * 
     * @param srting $name  -> Название
     * @return string
     */
    private function quoteName(string $name)
    {
        return '`' . $this->quote($name, true) . '`';
    }
    
    /**
     * Выполняет запрос к базе данных
     * @param string $sql
     * @return mixed
     */
    private function query($sql)
    {
        // TODO
        // Лог долгих запросов
        $startTimeSql = microtime(true);
        $result       = $this->mysqli->query($sql);
        $timeSql      = microtime(true) - $startTimeSql;
        
        return $result;
    }
    
    /**
     * Добавляет запись в бд
     * 
     * @param array $data
     * @return mixed
     */
    private function insertSQL(string $table, array $data)
    {
        $keys = array_keys($data);
        foreach($keys as &$key) {
            $key = $this->quoteName($key);
        }
        $names  = implode(',', $keys);
        $values = '';
        foreach($data as $row) {
            if($row === null) {
                $values .= 'null,';
            } else {
                $values .= $this->quote($row) . ',';
            }
        }
        
        $fixValues = substr($values, 0, -1);
        $fixTable  = $this->quoteName($table);
        return $this->query("insert into $fixTable ($names) values ($fixValues)");
    }
    
    /** {@inheritdoc} */
    public function removeAccess(int $userId) : int
    {
        $fixTable  = $this->quoteName($this->dbTable);
        $fixUserId = $this->quote($userId);
        return $this->query("update $fixTable set `active` = 0 where `user_id` = $fixUserId");
    }


    /** {@inheritdoc} */
    public function accessFor(string $tokenHex) : bool
    {
        $fixTable    = $this->quoteName($this->dbTable);
        $fixTokenHex = $this->quote($tokenHex);
        return $this->query("update $fixTable set `active` = 1 where `token_hex` = $fixTokenHex limit 1") > 0;
    }
    
    /** {@inheritdoc} */
    public function removeAccessFor(string $tokenHex) : bool
    {
        $fixTable    = $this->quoteName($this->dbTable);
        $fixTokenHex = $this->quote($tokenHex);
        return $this->query("update $fixTable set `active` = 0 where `token_hex` = $fixTokenHex limit 1") > 0;
    }
    
    /**
     * Возвращает данные токена в иде массива
     * @param string $tokenHex -> Хеш токена
     * @return array|null
     */
    private function getTokenDataByHex(string $tokenHex) : ?object
    {
        $fixTable    = $this->quoteName($this->dbTable);
        $tokenHexFix = $this->quote($tokenHex);
        $res         = $this->query("select * from $fixTable where `token_hex` = $tokenHexFix and `active` = 1 limit 1");
        
        if($res && $data = $res->fetch_object()) {
            return $data;
        }
        
        return null;
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
                    $data->id,
                    $data->token_hex,
                    $data->user_id,
                    $data->user_signature,
                    $data->user_ip,
                    $data->user_mac,
                    $data->expires,
                    $data->create_time,
                    unserialize($data->permissions),
                    unserialize($data->buffer)
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
            throw new MySQLiTokenDataBaseException('не удалось создать токен');
        }
        
        $data = [
            'id'             => 0,
            'token_hex'      => $tokenHex,
            'user_id'        => $userId,
            'user_signature' => $userSignature,
            'user_ip'        => $userIp,
            'user_mac'       => $userMac,
            'expires'        => $expires,
            'create_time'    => $createTime,
            'permissions'    => serialize($permissions),
            'buffer'         => serialize($buf),
            'active'         => 1
        ];
        
        if($this->insertSQL($this->dbTable, $data) === true) {
            $id = $this->mysqli->insert_id;
            $factory = "$typeOf::factory";
            return $factory(
                $this,
                $id,
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
        throw new MySQLiTokenDataBaseException('не удалось создать токен');
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
        
        $log = [
            'id'             => 0,
            'token_id'       => $dbId,
            'user_id'        => $userId,
            'user_signature' => $userSignature,
            'user_ip'        => $userIp,
            'user_mac'       => $userMac,
            'is_expires'     => $isExpires,
            'data'           => serialize($data),
            'check'          => $check,
            'time'           => $time,
        ];
        
        $this->insertSQL($this->dbAuthLogTable, $log);
    }
    
    /**
     * Название таблицы
     * @return string
     */
    public function getTableName() : string
    {
        return $this->dbTable;
    }
    
    /**
     * Название таблицы с логами авторизации
     * @return string
     */
    public function getAuthLogTableName() : string
    {
        return $this->dbAuthLogTable;
    }
}