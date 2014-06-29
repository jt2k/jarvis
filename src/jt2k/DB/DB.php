<?php
namespace jt2k\DB;

class DB
{
    protected $pdo;
    protected $statement;

    public static function dsnMySQL($host, $schema)
    {
        return "mysql:host={$host};dbname={$schema}";
    }

    public function __construct($dsn, $username, $password)
    {
        $this->pdo = new \PDO($dsn, $username, $password);
    }

    public function setErrorMode($mode) {
        switch ($mode) {
            case 'silent':
                $pdo_mode = \PDO::ERRMODE_SILENT;
                break;
            case 'warning':
                $pdo_mode = \PDO::ERRMODE_WARNING;
                break;
            case 'exception':
                $pdo_mode = \PDO::ERRMODE_EXCEPTION;
                break;
            default:
                return;
        }
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, $pdo_mode);
    }

    public function getLastQuery()
    {
        if (is_object($this->statement)) {
            return $this->statement->queryString;
        } else {
            return false;
        }
    }

    public function getRow($sql, $bindings = array())
    {
        $this->execute($sql, $bindings);
        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }

    public function getRows($sql, $bindings = array())
    {
        $this->execute($sql, $bindings);
        return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getColumn($sql, $bindings = array())
    {
        $this->execute($sql, $bindings);
        return $this->statement->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
    
    public function getValue($sql, $bindings = array())
    {
        $column = $this->getColumn($sql, $bindings);
        if (is_array($column) && count($column) > 0) {
            return $column[0];
        } else {
            return false;
        }
    }

    public function getLoop($sql, $bindings = array())
    {
        return new DBLoop($this, $sql, $bindings);
    }

    public function getStatement()
    {
        return $this->statement;
    }

    public function execute($sql, $bindings = array())
    {
        if (is_scalar($bindings)) {
            $bindings = array($bindings);
        }
        $this->statement = $this->pdo->prepare($sql);
        if (count($bindings) > 0) {
            foreach ($bindings as $k => $v) {
                if (is_int($k)) {
                    $key = $k + 1;
                } else {
                    $key = $k;
                }
                if (is_array($v)) {
                    $this->statement->bindValue($key, $v[0], $v[1]);
                } else {
                    $this->statement->bindValue($key, $v);
                }
            }
        }
        return $this->statement->execute();
    }

    public function insert($table, $data, $command = "INSERT")
    {
        $columns = array();
        foreach (array_keys($data) as $column) {
            $columns[] = "`{$column}`";
        }
        $columns = join(', ', $columns);
        $placeholders = join(', ', array_fill(0, count($data), '?'));
        $bindings = array_values($data);
        return $this->execute("{$command} INTO `{$table}` ({$columns}) VALUES ({$placeholders})", $bindings);
    }

    public function insertIgnore($table, $data)
    {
        return $this->insert($table, $data, "INSERT IGNORE");
    }

    public function replace($table, $data)
    {
        return $this->insert($table, $data, "REPLACE");
    }

    // update is array of columns from the data keys that should be updated on duplicate key
    public function insertUpdate($table, $data, $update)
    {
        $columns = array();
        foreach (array_keys($data) as $column) {
            $columns[] = "`{$column}`";
        }
        $columns = join(', ', $columns);
        $placeholders = join(', ', array_fill(0, count($data), '?'));
        $bindings = array_values($data);

        $sets = array();
        foreach ($update as $column) {
            if (!array_key_exists($column, $data)) {
                continue;
            }
            $bindings[] = $data[$column];
            $sets[] = "`{$column}` = ?";
        }
        $sets = join(', ', $sets);

        return $this->execute("INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders}) ON DUPLICATE KEY UPDATE {$sets}", $bindings);

    }

    public function update($table, $data, $where)
    {
        $bindings = array();
        $sets = array();
        $wheres = array();
        foreach ($data as $column => $value) {
            $bindings[] = $value;
            $sets[] = "`{$column}` = ?";
        }
        $sets = join(', ', $sets);
        
        foreach ($where as $column => $value) {
            $bindings[] = $value;
            $wheres[] = "`{$column}` = ?";
        }
        $wheres = join(' AND ', $wheres);
        
        return $this->execute("UPDATE `{$table}` SET {$sets} WHERE {$wheres}", $bindings);
    }
}