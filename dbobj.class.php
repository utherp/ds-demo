<?php

class DBObj {

    // just gets the db connection, returns it to ->connection, which verifies and/or creates the table
    private static function _connection() {
        static $_conn = false;
        if (!$_conn) {
            $conf = include('db.php');
            $dsn = $conf['engine'] . ':' . 'host=' . $conf['host'] . ';dbname=' . $conf['dbname'];

            // I'm not bothering to catch stuff for such a simple example... but this is where it'd be done
            $_conn = new PDO($dsn, $conf['user'], $conf['pass'], [ PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ]);
        }
        return $_conn;
    }

    private function connection() {
        $pdo = static::_connection();
        $table = static::$db_table;

        $tbl = $pdo->query("show tables like '$table'")->fetchColumn();
        if (!$tbl) $this->create_table();
        return $pdo;
    }

    private function create_table () {
        $pdo = static::_connection();
        $query = 'create table ' . static::$db_table . ' ' .
                 '(' . static::$db_primary_key . ' int not null auto_increment, index(' . static::$db_primary_key .  ')';
        foreach (static::$db_fields as $k => $t) $query .= ", $k $t";
        $query .= ')';
        // TODO: add checking here
        $pdo->exec($query);

        // check for preload data.
        // there is a better way to do this, but again.... demo
        if (property_exists(get_class($this), 'db_preload_file') && file_exists(static::$db_preload_file)) {
            $rows = unserialize(file_get_contents(static::$db_preload_file));
            foreach ($rows as $data) {
                $obj = new static();
                foreach ($data as $k => $v) $obj->$k = $v;
                $obj->save();
            }
        }
    }

    private $_data = [];
    private $_changed = [];
    private $_loaded = false;

    private function _set_data ($data) {
        $this->_data = $data;
        $this->_loaded = true;
    }

    public function __set($name, $value) {
        // TODO: this should also have an 'unpack' equivalent, for what to do with the data after loading from the database
        $setname = '_set_' . $name;
        if (method_exists($this, $setname)) $value = $this->$setname($value);
        if (!$this->_loaded) return $this->_data[$name] = $value;
        if ($this->_data[$name] !== $value) $this->_changed[$name] = $value;
    }

    public function __get($name) {
        return @key_exists($name, $this->_changed) ? $this->_changed[$name] : $this->_data[$name];
    }
    public function __isset($name) {
        return (key_exists($name, $this->_changed) || key_exists($name, $this->_data));
    }
    public function __unset($name) {
        if (key_exists($name, $this->_changed)) unset($this->_changed[$name]);
        else unset($this->_data[$name]);
    }

    private function create () {
        if ($this->_loaded) return $this->save();
        $pdo = $this->connection();
        $query = 'INSERT INTO ' . static::$db_table . 
                 '(' . implode(', ', array_keys($this->_data)) . ') ' .
                 'VALUES (' . rtrim(str_repeat('?,', count($this->_data)), ',') . ')';
        $prep = $pdo->prepare($query);
        $prep->execute(array_values($this->_data));
        if ($pdo->errorCode() !== '00000') {
            throw new Exception('Failed to save row: ' . $pdo->errorInfo()[2]);
        }
        $pri = static::$db_primary_key;
        $this->$pri = $pdo->lastInsertId();
        $this->_loaded = true;
        return;
    }

    public function save() {
        if (!$this->_loaded) return $this->create();
        if (!count($this->_changed)) return true;
        $pdo = $this->connection();

        $query = 'UPDATE ' . static::$db_table . ' SET ';
        $values = [];
        foreach ($this->_changed as $k => $v) {
            if (count($values)) $query .= ', ';
            $query .= $k . ' = ? ';
            $values[] = $v;
        }
        $pri = static::$db_primary_key;
        $query .= ' WHERE ' . $pri . ' = ?';
        $values[] = $this->$pri;
        $prep = $pdo->prepare($query);

        $prep->execute($values);
        if ($pdo->errorCode() !== '00000')
            throw new Exception('Failed to save row: ' . $pdo->errorInfo()[2]);
        return;
    }

    public function fetch () {
        $prep = $this->_fetch($this->_data);
        $data = $prep->fetch();
        if (!$data) return false;
        $this->_data = $data;
        $this->_loaded = true;
        return true;
    }

    public function fetchAll() {
        $prep = $this->_fetch($this->_data);
        $rows = $prep->fetchAll();
        if (!$rows) return false;
        $objs = [];
        foreach ($rows as $data) {
            $obj = new static();
            $obj->_set_data($data);
            $objs[] = $obj;
        }
        return $objs;
    }

    private function _fetch($data) {
        if (!is_array($data)) $data = [ static::$db_primary_key => $data ];
        if (!count($data)) throw new Exception('No query fields provided');

        $fields = array_keys(static::$db_fields);
        $query = 'select ' . implode(', ', $fields) . ' from ' . static::$db_table . ' where 1=1';
        $values = [];

        foreach ($data as $k => $v) {
            if (!key_exists($k, static::$db_fields)) {
                throw new Exception('Unknown field "' . $k . '", fields are: ' . implode(', ', $fields));
            }
            $query .= ' AND ' . $k . ' = ?';
            $values[] = $v;
        }
        $pdo = $this->connection();
        $prep = $pdo->prepare($query);
        $prep->execute($values);
        return $prep;
    }
 
}


