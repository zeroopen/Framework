<?php

class Db {
	
	private static $config = array();
	
	private static $db;
	
	public static function init($mode='') {
		if (empty(self::$db)) {
			$config = config::load('db', true);
			$driver = to_camelize($config['driver'], true);
			require LIB_PATH . 'Db/'.$driver.'Driver.php';
			if (!empty($mode)) {
				$mode_driver = $driver . ucfirst(strtolower($mode));
				require LIB_PATH . 'Db/'.$mode_driver.'Driver.php';
				self::$db = new $mode_driver();
			} else {
				self::$db = new $driver();
				self::toConnect($config);
			}
		}
		return self::$db;
	}
	
	public static function randKey($array) {
		$key = array_rand($array);
		return empty($array[$key]) ? false : $array[$key];
	}
	
	private static function toConnect($config) {
		$group = $config['group'];
		self::$config = ($group !== false) ? $config['info'][$group] : loop(array(__CLASS__, 'randKey'), array($config['info']));
		self::$db->setConnect('master', self::$config);
		self::$db->prefix(self::$config['prefix']);
		if (!empty(self::$config['slaves'])) {
			$slave = loop(array(__CLASS__, 'randKey'), array(self::$config['slaves']), 10);
			if (!empty($slave['name'])) {
				isset($slave['host']) || $slave['host'] = self::$config['host'];
				isset($slave['user']) || $slave['user'] = self::$config['user'];
				isset($slave['password']) || $slave['password'] = '';
				isset($slave['port']) || $slave['port'] = 3306;
				isset($slave['charset']) || $slave['charset'] = self::$config['charset'];
				self::$db->setConnect('slave', $slave);
			}
		}
	}
	
}

abstract class DbDriver {
	
	protected $conn, $read;
	
	protected $prefix, $table, $where, $operator, $order, $limit;
	
	protected $safe = false;
	
	abstract protected function connect($config);
	
	public function setConnect($name, $config) {
		if ($name == 'master' || $slaves == 'slave') {
			$conn = $this->connect($config);
			if ($conn === false) {
				exit;
			}
			switch($name) {
				case 'master':
					$this->conn = $conn;
					break;
				case 'slave':
					$this->read = $conn;
					break;
			}
			return true;
		}
		return false;
	}
	
	abstract protected function execute($sql, $array, $write=true);
	
	abstract protected function result($is_one);
	
	abstract protected function getParamSql($params, $operator);
	
	protected function getWhereSql() {
		$where = $this->where;
		if (is_array($where)) {
			if (is_assoc($where)) {
				$sql = $this->getParamSql($where, ' AND ');
			} else {
				$sql = '';
				$num = count($where);
				foreach($where as $key => $value) {
					$sql .= '(' . $this->getParamSql($value, ' AND ') . ')';
					if ($key < $num - 1) {
						$sql .= ' OR ';
					}
				}
			}
			return ' WHERE ' . $sql;
		} else if (is_string($where)) {
			return ' WHERE ' . $where;
		}
		return '';
	}
	
	protected function getParams($params) {
		if (is_array($params)) {
			if (!is_assoc($params)) {
				$result = array();
				foreach($params as $array) {
					foreach($array as $key => $value) {
						if (isset($result[$key])) {
							$key += '_1';
						}
						$result[$key] = $value;
					}
				}
				return $result;
			}
			return $params;
		}
		return array();
	}
	
	abstract public function query($sql, $result=true);
	
	abstract public function select($fields='*');
	
	abstract public function insert($data, $type='INSERT');
	
	abstract public function update($data);
	
	abstract public function delete();
	
	protected function reset() {
		$this->where = null;
		$this->order = null;
		$this->limit = null;
	}
	
	public function prefix($prefix) {
		$this->prefix = $prefix;
	}
	
	public function table($table = '', $prefix = null) {
		if (!empty($table)) {
			$this->safe = false;
			if ($prefix === null) $prefix = $this->prefix;
			$this->table = $this->escape($prefix . $table);
			return $this;
		} else {
			return $this->query('SHOW TABLES', false);
		}
	}
	
	public function safe($safe) {
		$this->safe = $safe ? true : false;
		return $this;
	}
	
	public function where($where) {
		if (!empty($where)) {
			$this->where = $where;
		}
		return $this;
	}
	
	public function order($order) {
		if (!empty($order)) {
			$this->order = " ORDER BY {$order}";
		}
		return $this;
	}
	
	public function page($page, $pagesize = 10) {
		if (is_numeric($page)) {
			$start = ($page-1) * $pagesize;
			$this->limit("{$start}, {$pagesize}");
		}
		return $this;
	}
	
	public function limit($limit) {
		$this->limit = " LIMIT {$limit}";
		return $this;
	}
	
	protected function isOne() {
		return ($this->limit === 1) ? true : false;
	}
	
	protected function escape($keyword) {
		if ($this->safe) {
			if (is_array($keyword)) {
				foreach($keyword as $k => $v) {
					$keyword[$k] = $this->escape($v);
				}
			}
			if (is_string($keyword)) {
				$keyword = '`'.$keyword.'`';
			}
		}
		return $keyword;
	}
	
}