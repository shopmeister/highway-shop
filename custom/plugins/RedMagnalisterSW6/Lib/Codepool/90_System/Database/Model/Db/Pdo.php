<?php

MLFilesystem::gi()->loadClass("model_db_abstract");

class ML_Database_Model_Db_Pdo extends ML_Database_Model_Db_Abstract {

    /**
     * @var false|PDO
     */
    protected $oInstance = null;
    
    /**
     * @var PDOStatement|null
     */
    protected $lastStatement = null;
    
    /**
     * @var int
     */
    protected $lastAffectedRows = 0;

    protected $access = array(
        'type' => '', // [pipe|socket|tcpip]
        'host' => '',
        'user' => '',
        'pass' => '',
        'port' => '', // will only be explicitly set for tcpip connections
        'sock' => '', // will only be explicitly set for non tcpip connections, includes windows pipes
        'persistent' => false,
    );

    public function __construct($access) {
        $this->access = array_merge($this->access, $access);
        $this->detectConnectionType();
    }

    protected function detectConnectionType() {
        // ... existing code ...
    }

    public function isConnected() {
        return $this->oInstance !== null;
    }

    public function connect() {
        try {
            $dsn = "mysql:host={$this->access['host']};port={$this->access['port']}";
            if (!empty($this->access['sock'])) {
                $dsn .= ";unix_socket={$this->access['sock']}";
            }
            $options = [
                PDO::ATTR_PERSISTENT => $this->access['persistent'],
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];
            $this->oInstance = new PDO($dsn, $this->access['user'], $this->access['pass'], $options);
            if (!empty($this->charset)) {
                $this->setCharset($this->charset);
            }
        } catch (PDOException $e) {
            throw new Exception("Can not connect to database: " . $e->getMessage());
        }
    }

    public function close() {
        $this->oInstance = null;
    }

    public function getLastErrorMessage() {
        return $this->oInstance ? $this->oInstance->errorInfo()[2] : '';
    }

    public function getLastErrorNumber() {
        return $this->oInstance ? $this->oInstance->errorCode() : 0;
    }

    public function getServerInfo() {
        return $this->isConnected() ? $this->oInstance->getAttribute(PDO::ATTR_SERVER_VERSION) : false;
    }

    public function setCharset($charset) {
        $this->charset = $charset;
        if ($this->isConnected()) {
            $this->oInstance->exec("SET NAMES '{$this->charset}'");
        }
    }

    public function query($query) {
        if ($this->isConnected()) {
            $trimmedQuery = trim(strtoupper($query));
            // Use exec() for UPDATE, DELETE, INSERT queries to get affected rows
            if (strpos($trimmedQuery, 'UPDATE') === 0 || 
                strpos($trimmedQuery, 'DELETE') === 0 || 
                strpos($trimmedQuery, 'INSERT') === 0) {
                $this->lastAffectedRows = $this->oInstance->exec($query);
                return $this->lastAffectedRows !== false;
            } else {
                $this->lastAffectedRows = 0;
                return $this->oInstance->query($query);
            }
        }
        return false;
    }

    public function escape($str) {
        if ($this->isConnected()) {
            // Use PDO's quote method and trim the surrounding quotes
            $quoted = $this->oInstance->quote($str);
            return substr($quoted, 1, -1);
        }
        return self::fallbackEscape($str);
    }

    public function affectedRows() {
        return $this->lastAffectedRows;
    }

    public function getInsertId() {
        return $this->isConnected() ? $this->oInstance->lastInsertId() : false;
    }

    public function isResult($m) {
        return $m instanceof PDOStatement;
    }

    public function numRows($result) {
        return $result->rowCount();
    }

    public function fetchArray($result) {
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function freeResult($result) {
        // PDO does not require explicit freeing of results
    }
}