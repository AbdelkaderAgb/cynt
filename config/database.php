<?php
/**
 * CYN Tourism Management System - Enhanced Database Class
 * 
 * This class provides secure database operations using PDO with prepared statements.
 * Features:
 * - Full PDO prepared statement support
 * - Transaction handling
 * - Query logging and debugging
 * - Batch insert operations
 * - Connection pooling
 * - Error handling with exceptions
 * 
 * @author CYN Tourism
 * @version 2.0.0
 */

// Prevent direct access
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

class Database {
    
    /** @var PDO|null Database connection instance */
    private static $instance = null;
    
    /** @var PDO|null Current PDO connection */
    private $connection = null;
    
    /** @var PDOStatement|null Last executed statement */
    private $lastStatement = null;
    
    /** @var array Query log for debugging */
    private $queryLog = [];
    
    /** @var bool Enable query logging */
    private $loggingEnabled = false;
    
    /** @var int Maximum log entries */
    private $maxLogEntries = 100;
    
    /** @var int Total query count */
    private $queryCount = 0;
    
    /** @var float Total query execution time */
    private $totalQueryTime = 0;
    
    /** @var bool Transaction status */
    private $inTransaction = false;
    
    /**
     * Private constructor (Singleton pattern)
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Get database instance (Singleton)
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     * 
     * @throws PDOException
     */
    private function connect() {
        try {
            $driver = defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
            
            if ($driver === 'sqlite') {
                $dbPath = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../database/cyn_tourism.sqlite';
                $dsn = "sqlite:$dbPath";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                ];
                
                $this->connection = new PDO($dsn, null, null, $options);
                $this->connection->exec("PRAGMA journal_mode=WAL");
                $this->connection->exec("PRAGMA foreign_keys=ON");
                
                // Register MySQL-compatible functions for SQLite
                $this->connection->sqliteCreateFunction('NOW', function() {
                    return date('Y-m-d H:i:s');
                }, 0);
                $this->connection->sqliteCreateFunction('CURDATE', function() {
                    return date('Y-m-d');
                }, 0);
                $this->connection->sqliteCreateFunction('MONTH', function($date) {
                    return date('n', strtotime($date));
                }, 1);
                $this->connection->sqliteCreateFunction('YEAR', function($date) {
                    return date('Y', strtotime($date));
                }, 1);
                $this->connection->sqliteCreateFunction('DATE_SUB', function($date, $interval) {
                    return date('Y-m-d H:i:s', strtotime($date . ' -' . $interval));
                }, 2);
                $this->connection->sqliteCreateFunction('CONCAT', function() {
                    return implode('', func_get_args());
                });
                $this->connection->sqliteCreateFunction('DATE_FORMAT', function($date, $format) {
                    $map = ['%Y' => 'Y', '%m' => 'm', '%d' => 'd', '%H' => 'H', '%i' => 'i', '%s' => 's', '%M' => 'F', '%b' => 'M', '%W' => 'l', '%a' => 'D', '%Y-%m' => 'Y-m'];
                    $phpFmt = str_replace(array_keys($map), array_values($map), $format);
                    return date($phpFmt, strtotime($date));
                }, 2);
            } else {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATE,
                    PDO::MYSQL_ATTR_COMPRESS => true,
                    PDO::ATTR_PERSISTENT => true
                ];
                
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                $this->connection->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
                $this->connection->exec("SET SESSION time_zone = '+00:00'");
            }
            
        } catch (PDOException $e) {
            $this->logError('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed. Please try again later.');
        }
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * Execute a prepared query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement|false
     */
    public function query($sql, $params = []) {
        $startTime = microtime(true);
        
        try {
            $stmt = $this->connection->prepare($sql);
            
            if (!$stmt) {
                throw new PDOException('Failed to prepare statement');
            }
            
            // Bind parameters with proper types
            foreach ($params as $key => $value) {
                $type = $this->getPDOType($value);
                $stmt->bindValue(is_int($key) ? $key + 1 : ":$key", $value, $type);
            }
            
            $stmt->execute();
            
            $this->lastStatement = $stmt;
            $this->queryCount++;
            
            $executionTime = microtime(true) - $startTime;
            $this->totalQueryTime += $executionTime;
            
            if ($this->loggingEnabled) {
                $this->logQuery($sql, $params, $executionTime);
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->logError('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Database query failed. Please try again later.');
        }
    }
    
    /**
     * Execute a query (static alias for query)
     * Allows Database::execute($sql, $params)
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return PDOStatement
     */
    public static function execute($sql, $params = []) {
        return self::getInstance()->query($sql, $params);
    }

    /**
     * Execute SELECT query and return all rows
     * Works as both Database::fetchAll() and $db->fetchAll()
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array
     */
    public static function fetchAll($sql, $params = []) {
        $stmt = self::getInstance()->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute SELECT query and return single row
     * Works as both Database::fetchOne() and $db->fetchOne()
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array|false
     */
    public static function fetchOne($sql, $params = []) {
        $stmt = self::getInstance()->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Execute SELECT query and return single column
     * Works as both Database::fetchColumn() and $db->fetchColumn()
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return mixed
     */
    public static function fetchColumn($sql, $params = []) {
        $stmt = self::getInstance()->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Execute SELECT query and return key-value pairs
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param string $keyColumn Column to use as key
     * @param string $valueColumn Column to use as value
     * @return array
     */
    public function fetchPairs($sql, $params = [], $keyColumn = null, $valueColumn = null) {
        $results = self::fetchAll($sql, $params);
        $pairs = [];
        
        foreach ($results as $row) {
            if ($keyColumn && $valueColumn) {
                $pairs[$row[$keyColumn]] = $row[$valueColumn];
            } elseif (count($row) >= 2) {
                $values = array_values($row);
                $pairs[$values[0]] = $values[1];
            }
        }
        
        return $pairs;
    }
    
    /**
     * Insert a record
     * Supports two calling modes:
     *   Database::insert('table_name', ['col' => 'val'])  -- table + data
     *   Database::insert('INSERT INTO ...', [params])     -- raw SQL
     * 
     * @param string $tableOrSql Table name or raw SQL
     * @param array $dataOrParams Column data or bind parameters
     * @return int Last insert ID
     */
    public static function insert($tableOrSql, $dataOrParams = []) {
        $instance = self::getInstance();
        
        // Raw SQL mode: first arg starts with INSERT/REPLACE
        if (preg_match('/^\s*(INSERT|REPLACE)\s/i', $tableOrSql)) {
            $instance->query($tableOrSql, $dataOrParams);
            return (int) $instance->getConnection()->lastInsertId();
        }
        
        // Table-based mode: insert($table, $data)
        $table = str_replace('`', '', $tableOrSql);
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new \Exception('Invalid table name');
        }
        
        $columns = array_keys($dataOrParams);
        $values = array_values($dataOrParams);
        
        $columnStr = implode('`, `', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        
        $sql = "INSERT INTO `{$table}` (`{$columnStr}`) VALUES ({$placeholders})";
        
        $instance->query($sql, $values);
        
        return (int) $instance->getConnection()->lastInsertId();
    }
    
    /**
     * Batch insert multiple records
     * 
     * @param string $table Table name
     * @param array $data Array of associative arrays
     * @param int $batchSize Number of records per batch
     * @return int Total inserted rows
     */
    public function batchInsert($table, $data, $batchSize = 100) {
        if (empty($data)) {
            return 0;
        }
        
        $table = $this->sanitizeTableName($table);
        $totalInserted = 0;
        
        // Get columns from first row
        $columns = array_keys($data[0]);
        $columnStr = implode('`, `', $columns);
        
        // Process in batches
        $batches = array_chunk($data, $batchSize);
        
        foreach ($batches as $batch) {
            $placeholders = [];
            $values = [];
            
            foreach ($batch as $row) {
                $rowPlaceholders = array_fill(0, count($columns), '?');
                $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
                
                foreach ($columns as $column) {
                    $values[] = $row[$column] ?? null;
                }
            }
            
            $sql = "INSERT INTO `{$table}` (`{$columnStr}`) VALUES " . implode(', ', $placeholders);
            
            $this->query($sql, $values);
            $totalInserted += count($batch);
        }
        
        return $totalInserted;
    }
    
    /**
     * Update records
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause
     * @param array $whereParams WHERE parameters
     * @return int Number of affected rows
     */
    public static function update($table, $data, $where, $whereParams = []) {
        $instance = self::getInstance();
        $table = $instance->sanitizeTableName($table);
        
        $setParts = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "`{$column}` = ?";
            $values[] = $value;
        }
        
        $setStr = implode(', ', $setParts);
        $sql = "UPDATE `{$table}` SET {$setStr} WHERE {$where}";
        
        // Merge parameters
        $allParams = array_merge($values, $whereParams);
        
        $stmt = $instance->query($sql, $allParams);
        
        return $stmt->rowCount();
    }
    
    /**
     * Delete records
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params WHERE parameters
     * @return int Number of affected rows
     */
    public function delete($table, $where, $params = []) {
        $table = $this->sanitizeTableName($table);
        
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Soft delete (set deleted_at timestamp)
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params WHERE parameters
     * @return int Number of affected rows
     */
    public function softDelete($table, $where, $params = []) {
        return $this->update($table, ['deleted_at' => date('Y-m-d H:i:s')], $where, $params);
    }
    
    /**
     * Check if record exists
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params WHERE parameters
     * @return bool
     */
    public function exists($table, $where, $params = []) {
        $table = $this->sanitizeTableName($table);
        
        $sql = "SELECT 1 FROM `{$table}` WHERE {$where} LIMIT 1";
        $result = $this->fetchColumn($sql, $params);
        
        return $result !== false;
    }
    
    /**
     * Count records
     * 
     * @param string $table Table name
     * @param string $where Optional WHERE clause
     * @param array $params Optional parameters
     * @return int
     */
    public function count($table, $where = '', $params = []) {
        $table = $this->sanitizeTableName($table);
        
        $sql = "SELECT COUNT(*) FROM `{$table}`";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        return (int) $this->fetchColumn($sql, $params);
    }
    
    /**
     * Get sum of column
     * 
     * @param string $table Table name
     * @param string $column Column to sum
     * @param string $where Optional WHERE clause
     * @param array $params Optional parameters
     * @return float
     */
    public function sum($table, $column, $where = '', $params = []) {
        $table = $this->sanitizeTableName($table);
        
        $sql = "SELECT SUM(`{$column}`) FROM `{$table}`";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        return (float) ($this->fetchColumn($sql, $params) ?? 0);
    }
    
    /**
     * Start a transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        if ($this->inTransaction) {
            throw new Exception('Transaction already started');
        }
        
        $this->inTransaction = true;
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit() {
        if (!$this->inTransaction) {
            throw new Exception('No active transaction to commit');
        }
        
        $this->inTransaction = false;
        return $this->connection->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback() {
        if (!$this->inTransaction) {
            throw new Exception('No active transaction to rollback');
        }
        
        $this->inTransaction = false;
        return $this->connection->rollBack();
    }
    
    /**
     * Execute callback within a transaction
     * 
     * @param callable $callback Function to execute
     * @return mixed Result from callback
     * @throws Exception
     */
    public function transaction(callable $callback) {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Check if in transaction
     * 
     * @return bool
     */
    public function inTransaction() {
        return $this->inTransaction;
    }
    
    /**
     * Get last insert ID
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Get number of affected rows from last query
     * 
     * @return int
     */
    public function rowCount() {
        return $this->lastStatement ? $this->lastStatement->rowCount() : 0;
    }
    
    /**
     * Enable query logging
     * 
     * @param bool $enabled
     */
    public function setLogging($enabled) {
        $this->loggingEnabled = (bool) $enabled;
    }
    
    /**
     * Get query log
     * 
     * @return array
     */
    public function getQueryLog() {
        return $this->queryLog;
    }
    
    /**
     * Get query statistics
     * 
     * @return array
     */
    public function getStats() {
        return [
            'query_count' => $this->queryCount,
            'total_time' => round($this->totalQueryTime, 4),
            'avg_time' => $this->queryCount > 0 ? round($this->totalQueryTime / $this->queryCount, 4) : 0
        ];
    }
    
    /**
     * Clear query log
     */
    public function clearLog() {
        $this->queryLog = [];
    }
    
    /**
     * Escape string for use in queries (use prepared statements instead)
     * 
     * @param string $string
     * @return string
     * @deprecated Use prepared statements instead
     */
    public function escape($string) {
        return substr($this->connection->quote($string), 1, -1);
    }
    
    /**
     * Quote identifier (table/column name)
     * 
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier) {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
    
    /**
     * Close database connection
     */
    public function close() {
        $this->connection = null;
        self::$instance = null;
    }
    
    /**
     * Get PDO type for value
     * 
     * @param mixed $value
     * @return int
     */
    private function getPDOType($value) {
        switch (gettype($value)) {
            case 'NULL':
                return PDO::PARAM_NULL;
            case 'boolean':
                return PDO::PARAM_BOOL;
            case 'integer':
                return PDO::PARAM_INT;
            default:
                return PDO::PARAM_STR;
        }
    }
    
    /**
     * Sanitize table name
     * 
     * @param string $table
     * @return string
     */
    private function sanitizeTableName($table) {
        // Remove any backticks to prevent injection
        $table = str_replace('`', '', $table);
        
        // Validate table name (alphanumeric and underscores only)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception('Invalid table name');
        }
        
        return $table;
    }
    
    /**
     * Log query for debugging
     * 
     * @param string $sql
     * @param array $params
     * @param float $time
     */
    private function logQuery($sql, $params, $time) {
        $this->queryLog[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => round($time, 4),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Keep log size limited
        if (count($this->queryLog) > $this->maxLogEntries) {
            array_shift($this->queryLog);
        }
    }
    
    /**
     * Log error
     * 
     * @param string $message
     */
    private function logError($message) {
        if (defined('LOG_ENABLED') && LOG_ENABLED) {
            $logFile = defined('LOG_PATH') ? LOG_PATH . 'database_errors.log' : __DIR__ . '/logs/database_errors.log';
            $logDir = dirname($logFile);
            
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            
            $logMessage = sprintf(
                "[%s] %s | IP: %s | URL: %s\n",
                date('Y-m-d H:i:s'),
                $message,
                $_SERVER['REMOTE_ADDR'] ?? 'CLI',
                $_SERVER['REQUEST_URI'] ?? 'N/A'
            );
            
            @file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
    }
    


    /**
     * Destructor - ensure connection is closed
     */
    public function __destruct() {
        if ($this->inTransaction) {
            $this->rollback();
        }
        $this->close();
    }
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Get database instance
 * 
 * @return Database
 */
function db() {
    return Database::getInstance();
}

/**
 * Execute a query
 * 
 * @param string $sql
 * @param array $params
 * @return PDOStatement
 */
function db_query($sql, $params = []) {
    return db()->query($sql, $params);
}

/**
 * Fetch all rows
 * 
 * @param string $sql
 * @param array $params
 * @return array
 */
function db_fetch_all($sql, $params = []) {
    return db()->fetchAll($sql, $params);
}

/**
 * Fetch single row
 * 
 * @param string $sql
 * @param array $params
 * @return array|false
 */
function db_fetch_one($sql, $params = []) {
    return db()->fetchOne($sql, $params);
}

/**
 * Fetch single column
 * 
 * @param string $sql
 * @param array $params
 * @return mixed
 */
function db_fetch_column($sql, $params = []) {
    return db()->fetchColumn($sql, $params);
}

/**
 * Insert record
 * 
 * @param string $table
 * @param array $data
 * @return int
 */
function db_insert($table, $data) {
    return db()->insert($table, $data);
}

/**
 * Update records
 * 
 * @param string $table
 * @param array $data
 * @param string $where
 * @param array $whereParams
 * @return int
 */
function db_update($table, $data, $where, $whereParams = []) {
    return db()->update($table, $data, $where, $whereParams);
}

/**
 * Delete records
 * 
 * @param string $table
 * @param string $where
 * @param array $params
 * @return int
 */
function db_delete($table, $where, $params = []) {
    return db()->delete($table, $where, $params);
}

/**
 * Check if record exists
 * 
 * @param string $table
 * @param string $where
 * @param array $params
 * @return bool
 */
function db_exists($table, $where, $params = []) {
    return db()->exists($table, $where, $params);
}

/**
 * Count records
 * 
 * @param string $table
 * @param string $where
 * @param array $params
 * @return int
 */
function db_count($table, $where = '', $params = []) {
    return db()->count($table, $where, $params);
}

/**
 * Execute within transaction
 * 
 * @param callable $callback
 * @return mixed
 */
function db_transaction(callable $callback) {
    return db()->transaction($callback);
}