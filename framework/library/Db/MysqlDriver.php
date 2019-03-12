<?php

class Mysql extends DbDriver {
	
	protected $prepare;
	
	protected function connect($config) {
		$conn = new mysqli($config['host'], $config['user'], $config['password'], $config['name'], $config['port']);
		if ($conn->connect_errno) {
			Log::record(Log::ERROR, $conn->connect_error);
			return false;
		}
		$conn->set_charset($config['charset']);
		return $conn;
	}
	
	protected function __destory() {
		if ($this->prepare instanceof mysqli_stmt) {
			$this->prepare->close();
		}
		$this->conn->close();
	}
	
	/**
	 * 执行sql操作
	 * @param string $sql sql语句
	 * @param array $array 回调数组
	 * @param boolean $write 是否需要写入数据
	 */
	protected function execute($sql, $array, $write = true) {
		$conn = ($write || empty($this->read)) ? $this->conn : $this->read;
		$this->prepare = $conn->prepare($sql);
		if (!empty($array)) {
			if (!call_user_func_array(array($this->prepare, 'bind_param'), $array)) {
				Log::record(Log::ERROR, "{$sql}, The arguments you pass are not legal!");
				Log::record(Log::ERROR, $array);
				exit;
			}
		}
		$this->prepare->execute();
		$this->reset();
	}
	
	/**
	 * 获取结果集
	 * @param int|string $is_one 是否只取一条数据
	 * @return array 结果集
	 */
	protected function result($is_one) {
		$array = array();
		if ($this->prepare instanceof mysqli_stmt) {
			$this->prepare->store_result();
			$variables = array();
			$data = array();
			$meta = $this->prepare->result_metadata();
			while($field = $meta->fetch_field()) {
				$variables[] = &$data[$field->name];
			}
			call_user_func_array(array($this->prepare, 'bind_result'), $variables);
			$i = 0;
			while($this->prepare->fetch()) {
				$one_array = array();
				foreach($data as $k => $v) {
					$one_array[$k] = $v;
				}
				if ($is_one) {
					$array = $one_array;
					break;
				}
				$array[$i] = $one_array;
				$i++;
			}
			$this->freeResult();
		} else if ($this->prepare instanceof mysqli_result) {
			if ($is_one) {
				$array = $this->prepare->fetch_assoc();
			} else {
				while($row = $this->prepare->fetch_assoc()) {
					$array[] = $row;
				}
			}
			$this->freeResult();
		}
		return $array;
	}
	
	/**
	 * 释放结果集
	 */
	protected function freeResult() {
		$this->prepare->free_result();
	}
	
	/**
	 * 获取数组所有值的数据类型
	 * @param array $array 数组
	 * @return string 数据类型集
	 */
	protected function getParamType($array) {
		$types = "";
		foreach($array as $key => $value) {
			if (is_int($value)) {
				$types .= 'i';
			} else if (is_float($value)) {
				$types .= 'd';
			} else if (is_string($value)) {
				$types .= 's';
			} else {
				$types .= 'b';
			}
		}
		return $types;
	}
	
	/*
	 * 获取参数绑定sql
	 * @param array $params 字段数组
	 * @param string $operator 运算符
	 */
	protected function getParamSql($params, $operator) {
		$string = '';
		$params = array_keys($params);
		$num = count($params);
		foreach($params as $key => $value) {
			$string .= $this->escape($value) . ' = ?';
			if ($key < $num - 1) {
				$string .= $operator;
			}
		}
		return $string;
	}
	
	/**
	 * 执行sql语句
	 * @param string $sql sql语句
	 * @param boolean true返回结果集，false返回query结果
	 */
	public function query($sql, $result = true) {
		$this->prepare = $this->conn->query($sql);
		if (!$result) {
			return $this->prepare;
		}
		return $this->result();
	}
	
	/**
	 * 预处理查询操作
	 * 提示：支持多接口查询
	 * @param string $fields 字段
	 * @return array 返回结果集
	 */
	public function select($fields = '*') {
		$table = $this->table;
		$sql = "SELECT {$fields} FROM {$table}";
		$array = array();
		$where = $this->where;
		if (!empty($where)) {
			$sql .= $this->getWhereSql();
			if (is_array($where)) {
				$where = $this->getParams($where);
				$array[] = $this->getParamType($where);
				foreach($where as $key => &$value) {
					$array[] = &$value;
				}
			}
		}
		$sql .= $this->order . $this->limit;
		$is_one = $this->isOne();
		$this->execute($sql, $array, false);
		return $this->result($is_one);
	}
	
	/**
	 * 预处理插入操作
	 * @param array $data 数据
	 * @param string $type 插入数据方式（可选：'INSERT', 'INSERT IGNORE', 'REPLACE'）
	 */
	public function insert($data, $type = 'INSERT') {
		$num = count($data);
		if (!is_array($data) || $num == 0) {
			return false;
		}
		$sql_fields = implode(', ', $this->escape(array_keys($data)));
		$sql_values = trim(str_repeat('?, ', $num), ', ');
		$array[] = $this->getParamType($data);
		foreach($data as $key => &$value) {
			$array[] = &$value;
		}
		$table = $this->table;
		$sql = "{$type} INTO {$table} ({$sql_fields}) VALUES ({$sql_values})";
		$this->execute($sql, $array);
	}
	
	/**
	 * 预处理修改操作
	 * @param array|string $data 需要修改的数据，支持数组和自定义语句
	 */
	public function update($data) {
		$where = $this->where;
		if (empty($where)) {
			return false;
		}
		$params = array();
		if (is_array($where)) {
			$params = is_assoc($where) ? array($where) : $where;
		}
		if (is_array($data)) {
			if (!is_assoc($data)) {
				return false;
			}
			array_unshift($params, $data);
			$sql_set = $this->getParamSql(array_keys($data), ', ');
		} else {
			$sql_set = $data;
		}
		$sql_where = $this->getWhereSql();
		$params = $this->getParams($params);
		$array[] = $this->getParamType($params);
		foreach($params as $key => &$value) {
			$array[] = &$value;
		}
		$table = $this->table;
		$sql = "UPDATE {$table} SET {$sql_set}{$sql_where}";
		$this->execute($sql, $array);
	}
	
	/**
	 * 预处理删除操作
	 */
	public function delete() {
		$where = $this->where;
		if (empty($where)) {
			return false;
		}
		$sql_where = $this->getWhereSql();
		$where = $this->getParams($where);
		$array[] = $this->getParamType($where);
		foreach($where as $key => &$value) {
			$array[] = &$value;
		}
		$table = $this->table;
		$sql = "DELETE FROM {$table}{$sql_where}";
		$this->execute($sql, $array);
	}
	
}
