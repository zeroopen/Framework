<?php

class StartController extends Controller {
	
	const MODEL = 'InstallModel';
	
	protected function _initialize() {
		$this->_config['doing'] = array('host', 'dbuname', 'dbpass', 'dbprefix', 'dbcharset', 'dbname', 'aduname', 'adpass', 'safecode');
	}
	
	public function doing() {
		if (!file_exists(SELF_PATH . 'verify.lock')) {
			$this->error(200, '非法请求');
		}
		$db['host'] = trim($this->post('host'));
		if (empty($db['host'])) {
			$this->error(200, '数据库地址不能为空');
		}
		$db['user'] = trim($this->post('dbuname'));
		if (empty($db['user'])) {
			$this->error(200, '数据库用户名不能为空');
		}
		$db['password'] = trim($this->post('dbpass'));
		$db['name'] = trim($this->post('dbname'));
		if (empty($db['name'])) {
			$this->error(200, '数据库名称不能为空');
		}
		if (strpos($db['name'], ' ') !== false) {
			$this->error(200, '数据库名称不允许存在空格');
		}
		$db['charset'] = trim($this->post('dbcharset'));
		$db['prefix'] = trim($this->post('dbprefix'));
		if (empty($db['prefix'])) {
			$this->error(200, '数据库表前缀不能为空');
		}
		$aname = trim($this->post('aduname'));
		if (empty($aname)) {
			$this->error(200, 'admin用户名不能为空');
		}
		$apass = trim($this->post('adpass'));
		if (empty($apass)) {
			$this->error(200, 'admin密码不能为空');
		}
		if (strlen($apass) < 8) {
			$this->error(200, 'admin密码过于简单');
		}
		if (strlen($apass) > 20) {
			$this->error(200, 'admin密码超出限定长度');
		}
		if (!preg_match('~^[A-Za-z][A-Za-z]*[a-z0-9_]*$~', $aname)) {
			$this->error(200, 'admin用户名必须以字母开头，只允许字母、数字、下划线');
		}
		if (!$this->model->connect($db)) {
			$this->error(200, '数据库连接失败');
		}
		$se_key = trim($this->post('safecode'));
		try {
			$table_file = APP_PATH . MODULE . '/Data/install.sql';
			$data_file = APP_PATH . MODULE . '/Data/install_data.sql';
			if (!file_exists($table_file) || !file_exists($data_file)) {
				$this->error(200, '数据库文件不存在！');
			}
			$this->model->setConfig($db, $se_key);
			$this->model->initTable(file_get_contents($table_file));
			$this->model->initData(file_get_contents($data_file));
			$this->model->createAdmin($aname, $apass);
			session('ok', Random::string(12));
			$this->ok(session('ok'));
		}
		catch(Exception $e) {
			$this->model->recover($db['name']);
			Log::record(Log::ERROR, $e->getMessage());
			$this->error(300, '安装失败！');
		}
	}
	
}
