<?php

class MysqlDev extends Mysql {
	
	public function testDb($host, $user, $password, $name='', $port=3306, $charset='utf8') {
		$config['host'] = $host;
		$config['user'] = $user;
		$config['password'] = $password;
		$config['name'] = $name;
		$config['port'] = $port;
		$config['charset'] = $charset;
		$this->conn = $this->connect($config);
		if ($this->conn === false) {
			return false;
		}
		return true;
	}
	
	public function selectDb($name) {
		return $this->conn->select_db($name);
	}
	
	public function createDb($name) {
		if ($this->selectDb($name)) {
			if (!$this->query("ALTER DATABASE `{$name}` DEFAULT CHARSET=utf8 COLLATE utf8_general_ci", false)) {
				return false;
			}
		} else if (!$this->query("CREATE DATABASE `{$name}` DEFAULT CHARSET=utf8 COLLATE utf8_general_ci", false)) {
			return false;
		}
		$this->selectDb($name);
		return true;
	}
	
	public function error() {
		return $this->conn->error;
	}
	
}