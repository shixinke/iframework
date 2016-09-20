<?php
namespace core;
use \PDO;
class Model
{
    protected static $_connection;
    const SUCCESS_CODE = '00000';
    private $sql;
    private $error;
    protected $condition = array();
    protected $sqlStates = array();
    public function __construct()
    {
        if (!class_exists('PDO')) {
            throw new \Exception('the PDO Extension is not installed');
            return false;
        }
        $this->connect();
    }

    protected function connect()
    {
        if (self::$_connection) {
            return self::$_connection;
        }
        $config = \core\Config::get('database');
        if (empty($config)) {
            throw new \Exception('the database config is empty');
            return false;
        }
        $dsn = $config['type'].':dbname='.$config['database'].';host='.$config['host'];
        try {
            self::$_connection = new \PDO($dsn, $config['user'], $config['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "'.$config['charset'].'"'));
        } catch (\PDOException $e) {
            throw new \Exception('connect failed:'.$e->getMessage());
            return false;
        }

    }

    public function find($table, $fields = null, $where = array())
    {
        $this->condition['limit'] = 1;
        if ($fields) {
            $this->fields($fields);
        }
        if (!empty($where)) {
            $this->where($where);
        }
        return $this->read($table);
    }

    public function select($table, $fields = null, $where = array())
    {
        if ($fields) {
            $this->condition['fields'] = $fields;
        }
        if ($where) {
            $this->where($where);
        }
        return $this->read($table);
    }

    public function insert($table, $data)
    {
        $sql = 'INSERT INTO '.$table.' ';
        $fields = array_keys($data);
        $values = array_values($data);
        $sql .= '(';
        foreach($fields as $v) {
            $sql .= '`'.$v.'`,';
        }
        $sql = trim($sql, ',');
        $sql .= ') VALUES (';
        foreach($values as $v) {
            $sql .= '"'.$v.'",';
        }
        $sql = trim($sql, ',').')';
        $res =  $this->execute($sql);
        $id = self::$_connection->lastInsertId();
        return $id ? $id : $res;
    }

    public function update($table, $data, $where = array())
    {
        $this->where($where);
        $this->generateSql();
        if (empty($this->condition['where']) && empty($where)) {
            $this->error = 'no condition in update operation';
            return false;
        }
        $sql = 'UPDATE '.$table.' SET ';
        foreach($data as $k=>$v) {
            $sql .= '`'.$k.'` = "'.$v.'",';
        }
        $sql = trim($sql, ',').' '.$this->sqlStates['where'];
        return $this->execute($sql);
    }

    public function delete($table, $where)
    {
        $this->where($where);
        $this->generateSql();
        if (empty($this->condition['where']) && empty($where)) {
            $this->error = 'no condition in delete operation';
            return false;
        }
        $sql = 'DELETE FROM '.$table.' ';
        $sql .= $this->sqlStates['where'];
        return $this->execute($sql);
    }

    public function where($field, $condition = null, $value = null)
    {
        if (is_array($field)) {
            foreach($field as $k=>$v) {
                $this->condition['where'][$k] = is_array($v) ? array($v[0], $v[1]) : array('=', $v);
            }
        } else {
            if (is_null($value)) {
                $this->condition['where'][$field] = is_null($condition) ? array('ISNULL', $condition) : array('=', $condition);
            } else {
                $this->condition['where']['field'] = array($condition, $value);
            }
        }
    }

    protected function generateSql()
    {
        if (isset($this->condition['fields'])) {
            if (!is_array($this->condition['fields'])) {
                if ($this->condition['fields'] != '*') {
                    $this->condition['fields'] = explode(',', $this->condition['fields']);
                }
            }
            $fields = '';
            if ($this->condition['fields'] == '*') {
                $fields = '*';
            } else {
                foreach($this->condition['fields'] as $field) {
                    $fields .= '`'.$field.'`,';
                }
            }
            $this->sqlStates['fields'] = trim($fields, ',');
            unset($fields);
        }
        if (isset($this->condition['where']) && !empty($this->condition['where'])) {
            $where = ' WHERE ';
            foreach($this->condition['where'] as $field=>$value) {
                switch(strtoupper($value[0])) {
                    case 'LIKE':
                        $where .= $field.' LIKE "%'.$value[1].'%",';
                        break;
                    case 'GT':
                        $where .= $field.' >='.$value[1];
                        break;
                    case 'LT':
                        $where .= $field.' <='.$value[0];
                        break;
                    case 'ISNULL':
                        $where .= ' ISNULL('.$field.')';
                        break;
                    case 'IN':
                        if (!is_array($value[1])) {
                            $value[1] = explode(',', $value[1]);
                        }
                        $where .= $field.' IN (';
                        foreach($value[1] as $v) {
                                $where .= '"'.$v.'",';
                        }
                        $where = trim($where, ',').')';
                    default:
                        if (is_string($value[1])) {
                            $where .= $field.$value[0].'"'.$value[1].'"';
                        } else {
                            $where .= $field.$value[0].$value[1];
                        }
                }
            }
            $this->sqlStates['where'] = trim($where, ',');
            unset($where);
        }
        if (isset($this->condition['limit'])) {
            $this->sqlStates['limit'] = ' LIMIT '.$this->condition['limit'];
        }
        if (isset($this->condition['order'])) {
            $orders = ' ORDER BY ';
            foreach($this->condition['order'] as $field=>$value) {
                $orders .= '`'.$field.'` '.$value.',';
            }
            $this->sqlStates['order'] = trim($orders, ',');
        }
        if (isset($this->condition['group'])) {
            $groups = ' GROUP BY ';
            foreach($this->condition['group'] as $field=>$value) {
                $groups = '`'.$field.'`,';
            }
            $this->sqlStates['group'] = trim($groups, ',');
        }
    }

    public function query($sql, $rows = true)
    {
        $res = $this->exec($sql);
        if ($res) {
            return $rows ? $res->fetchAll() : $res->fetch();
        }
        return false;
    }


    public function execute($sql)
    {
        $res = $this->exec($sql);
        return $res;
    }

    protected function read($table)
    {
        $rows = true;
        $this->generateSql();
        $sql = 'SELECT ';
        if (isset($this->sqlStates['fields'])) {
            $sql .= $this->sqlStates['fields'].' ';
        } else {
            $sql .= ' * ';
        }
        $sql .= ' FROM '.$table.' ';
        if (isset($this->sqlStates['where'])) {
            $sql .= ' '.$this->sqlStates['where'];
        }
        if (isset($this->sqlStates['group'])) {
            $sql .= ' '.$this->sqlStates['group'];
        }
        if (isset($this->sqlStates['order'])) {
            $sql .= ' '.$this->sqlStates['order'];
        }
        if (isset($this->sqlStates['limit'])) {
            $sql .= ' '.$this->sqlStates['limit'];
            $rows = $this->sqlStates['limit'] == 1 ? false : true;
        }

        return $this->query($sql, $rows);
    }


    protected function exec($sql)
    {
        $this->sql = $sql;
        $res = self::$_connection->prepare($sql);
        $res->execute();
        if (self::$_connection->errorCode() != self::SUCCESS_CODE) {
            $this->error = self::$_connection->errorInfo();
            return false;
        }
        return $res;
    }

    public function fields($fields)
    {
        $this->condition['fields'] = $fields;
        return $this;
    }

    public function limit($offset, $limit = 0)
    {
        $this->condition['limit'] = $limit > 0 ? $offset.','.$limit : $offset;
        return $this;
    }

    public function order($orderby)
    {
        if (is_array($orderby) && !empty($orderby)) {
            $this->condition['order'][$orderby[0]] = isset($orderby[1]) ? $orderby[1] : 'ASC';
        } else {
            $arr = explode(" ", trim($orderby));
            $this->condition['order'][$arr[0]] = isset($arr[1]) ? $arr[1] : 'ASC';
            unset($arr);
        }
        unset($orderby);
        return $this;
    }

    public function group($groupby)
    {
        if (!is_array($groupby)) {
            $groupby = explode(",", trim($groupby, ','));
        }
        if (!empty($groupby)) {
            foreach($groupby as $group) {
                $this->condition['group'][$group] = $group;
            }
        }
        return $this;
    }

    public function getLastSql()
    {
        return $this->sql;
    }

    public function getError()
    {
        return $this->error;
    }


}