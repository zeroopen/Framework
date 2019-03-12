<?php

class InstallModel {
	
	protected $prefix;
	
	public function connect($config) {
		$this->db = db::init('dev');
		if ($this->db->testDb($config['host'], $config['user'], $config['password'])) {
			if ($this->db->createDb($config['name'])) {
				return true;
			}
		}
		return false;
	}
	
	public function setConfig($db_info, $se_key) {
		$info = array(
			'host' => '127.0.0.1',
			'user' => 'root',
			'password' => '',
			'name' => '',
			'port' => 3306,
			'charset' => 'utf8',
			'prefix' => 'pre_',
			'slaves' => array()
		);
		$db['group'] = 1;
		$db['info'][1] = array_merge($info, $db_info);
		config::load('db');
		config::update('db', $db, true);
		config::remove('db');
		$system['upload']['key'] = Random::string(16);
		$system['verify_key'] = Random::string(12);
		$system['admin']['verify_key'] = Random::string(16);
		$system['admin']['se_key'] = $se_key;
		config::update('system', $system, true);
		$this->prefix = $db_info['prefix'];
	}
	
	public function initTable($content) {
		$prefix = $this->prefix;
		$content = str_replace('/*{prefix}*/', $prefix, $content);
		$content = str_replace(PHP_EOL, '', $content);
		$array = explode(';', $content);
		foreach($array as $sql) {
			if ($sql == '') continue;
			if (!$this->db->query($sql, false)) {
				Log::record(Log::ERROR, $this->db->error());
			}
		}
	}
	
	public function initData($content) {
		$prefix = $this->prefix;
		$content = str_replace('/*{prefix}*/', $prefix, $content);
		$content = preg_replace('~(;\n)|(;\r)~', '####', $content);
		$array = explode('####', $content);
		foreach($array as $sql) {
			if ($sql == '') continue;
			if (!$this->db->query($sql, false)) {
				Log::record(Log::ERROR, $this->db->error());
			}
		}
	}
	
	public function recover($name) {
		$this->db->query("DROP DATABASE " . $name, false);
	}
	
	public function createAdmin($admin, $password) {
		if (empty($admin) || empty($password)) {
			return false;
		}
		$params['id'] = 1;
		$params['admin'] = $admin;
		$params['addtime'] = time();
		$params['password'] = Encrypt::password($password, $params['addtime']);
		$params['status'] = 1;
		$this->db->table('admin', $this->prefix)->insert($params, 'REPLACE');
	}
	
}
