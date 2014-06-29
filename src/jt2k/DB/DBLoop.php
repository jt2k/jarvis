<?php
namespace jt2k\DB;

class DBLoop implements \Iterator, \Countable {

    protected $db;
    protected $sql;
    protected $bindings;
    protected $statement;
    protected $row;
    protected $counter;

    protected $rewind_requery = false;
    
    public function __construct($db, $sql, $bindings = array())
    {
        $this->db = $db;
        $this->sql = $sql;
        $this->bindings = $bindings;

        $this->db->execute($this->sql, $this->bindings);
        $this->statement = $this->db->getStatement();
    }
    
    public function count()
    {
        return $this->statement->rowCount();
    }
    
    public function current()
    {
        if (is_array($this->row)) {
            return $this->row;
        } else {
            return $this->next();
        }
    }
    
    public function rewind()
    {
        if ($this->rewind_requery) {
            // requery on subsequent rewinds.  not ideal, but needed since pdo mysql doesn't support scrollable cursors
            $this->db->execute($this->sql, $this->bindings);
            $this->statement = $this->db->getStatement();
        } else {
            $this->rewind_requery = true;
        }
        $this->counter = null;
        return $this->fetch();
    }
    
    public function key()
    {
        return $this->counter;
    }
    
    public function valid()
    {
        return is_array($this->row);
    }
    
    public function next()
    {
        return $this->fetch();
    }
    
    public function first()
    {
        $this->rewind();
        return $this->current();
    }

    protected function fetch()
    {
        $this->row = $this->statement->fetch(\PDO::FETCH_ASSOC);
        if ($this->row===false) {
            return null;
        } else {
            if (is_null($this->counter)) {
                $this->counter = 0;
            } else {
                $this->counter++;
            }
            return $this->row;
        }
    }
}
