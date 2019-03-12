<?php

class Controller {
	
	private $running = false; // 运行状态
	
	protected $model;
	protected $view;
	
	protected $_doing;
	protected $_config;
	private $_request;
	private $_header;
	
	// 模型类名
	const MODEL = 'Model';
	
	// 视图类名
	const VIEW = 'View';
	
	// 操作请求key
	const DO_KEY = 'do';
	
	/*
	 * 构造方法
	 */
	public function __construct() {
		$this->model = $this->initModel();
		$view = static::VIEW;
		$this->view = $view::getInstance();
		$this->header();
		$this->sandBox();
	}
	
	/*
	 * 运行控制器方法
	 * @param string $prefix 前缀名（可选）
	 */
	public function run($prefix='') {
		$this->_doing = empty($prefix) ? 'index' : (
			empty($this->_doing) ? (
				$this->getDoing($prefix) ? : ''
			) : $this->_doing
		);
		if (method_exists($this, '_initialize')) {
			$this->_initialize();
		}
		$this->checkHeader();
		$doing = $this->_doing;
		if (method_exists($this, $doing)) {
			$this->running = true;
			$this->$doing();
			return true;
		}
		return false;
	}
	
	protected function stop() {
		header("HTTP/1.1 404 Not Found");
		header("status: 404 Not Found");
		include ROOT_PATH . '404.html';
	}
	
	/*
	 * 获取请求标头，或设置强行检查标头（标头不存在时，将拒绝请求）
	 * @param string $name 请求标头
	 * @param string $value 标头值
	 */
	protected function header($name=null, $value=null) {
		if (!empty($name)) {
			if (strpos($name, '-') !== false) {
				$name = strtr(strtolower($name), '-', ' ');
				$name = strtr(ucwords($name), ' ', '-');
			}
			if ($value === null) {
				if (!isset($this->_request['header'][$name])) {
					return null;
				}
				return $this->_request['header'][$name];
			}
			$this->_header[$name] = $value;
		} else {
			$this->_request['header'] = getallheaders();
		}
	}
	
	/*
	 * 获取post请求数据
	 * @param string $key 键值（没有则返回全部数据）
	 * @param boolean $mode 是否启用严格模式（默认关闭）
	 * @return mixed
	 */
	protected function post($key=null, $mode=false) {
		if (!isset($this->_request['post'])) {
			$this->_request['post'] = isset($this->_config[$this->_doing]) ? $this->checkRequest($this->_config[$this->_doing], $_POST) : $_POST;
		}
		if (empty($key)) {
			return $this->_request['post'];
		}
		if (!isset($this->_request['post'][$key])) {
			$mode && $this->error(100, '非法请求');
			return null;
		}
		return $this->_request['post'][$key];
	}
	
	/*
	 * 获取get请求数据
	 * @param string $key 键值（没有则返回全部数据）
	 * @param boolean $mode 是否启用严格模式（默认关闭）
	 * @return mixed
	 */
	protected function get($key=null, $mode=false) {
		if (!isset($this->_request['get'])) {
			$this->_request['get'] = $_GET;
		}
		if (empty($key)) {
			return $this->_request['get'];
		}
		if (!isset($this->_request['get'][$key])) {
			$mode && $this->error(100, '非法请求');
			return null;
		}
		return $this->_request['get'][$key];
	}
	
	/*
	 * 检查验证码是否正确
	 * @param string $code 用户输入的验证码
	 * @param string $key 验证码所在session的键名
	 * @param string $se_key 安全前缀
	 * @return boolean
	 */
	protected function verifyCode($code, $key='', $se_key=null) {
		require_once LIB_PATH . 'Verify.php';
		$verify = new Verify();
		$verify->seKey = is_null($se_key) ? config::system('verify_key') : $se_key;
		return $verify->check($code, $key);
	}
	
	/*
	 * 请求标头安全检查
	 */
	private function checkHeader() {
		$config = $this->_header;
		$header = $this->_request['header'];
		if (!empty($config)) {
			foreach($config as $key => $value) {
				if (!isset($header[$key]) || $value !== $header[$key]) {
					$this->error(100, '非法请求');
				}
			}
		}
	}
	
	/**
	 * 请求数据安全检查
	 * @param array $array 配置数组
	 * @param array $res 请求数据
	 * @return array
	 */
	private function checkRequest($array, $res) {
		if (!is_array($array) || count($array) == 0) $this->error(200, '系统错误');
		$data = array();
		foreach($array as $key => $value) {
			$data[$value] = '';
		}
		$count = count($data);
		$num = 0;
		foreach($res as $key => $value) {
			if (!isset($data[$key])) $this->error(100, '非法请求');
			$data[$key] = $value;
			$num++;
		}
		if ($count != $num) $this->error(100, '非法请求');
		return $data;
	}
	
	/*
	 * 返回json信息
	 * @param int|string $code 状态码
	 * @param string $msg 信息
	 * @param mixed $data 数据
	 */
	protected function ajax($code, $msg, $data=null) {
		$params['code'] = $code;
		$params['msg'] = $msg;
		if ($data != null) {
			$params['data'] = $data;
		}
		echo json_encode($params);
	}
	
	/*
	 * 返回错误并退出
	 * @param int|string $code 状态码
	 * @param string $msg 信息
	 */
	protected function error($code, $msg) {
		if (IS_DOING) {
			$this->ajax($code, $msg);
			exit;
		} else {
			$this->view->assgin('seo', array('title' => '提示页'));
			$this->view->assgin('tips', $msg);
			$this->view->display('Common/tips');
			exit;
		}
	}
	
	/*
	 * 返回成功并退出
	 * @param string $msg 信息
	 * @param mixed $data 数据
	 */
	protected function ok($msg, $data=null) {
		if (IS_DOING) {
			$this->ajax(0, $msg, $data);
			exit;
		} else {
			$this->view->assgin('seo', array('title' => '提示页'));
			$this->view->assgin('tips', $msg);
			$this->view->display('Common/tips');
			exit;
		}
	}
	
	/*
	 * 返回模型实例
	 * @param string $model 模型名
	 * @param string $module 模块名
	 * @return object
	 */
	protected function model($model, $module = MODULE) {
		$model = $model . 'Model';
		return $this->loadModel($model, $module);
	}
	
	/*
	 * 初始化模型
	 * @return object
	 */
	protected function initModel() {
		if (static::MODEL == 'Model') {
			return new Model;
		}
		return $this->loadModel(static::MODEL, MODULE);
	}
	
	/*
	 * 加载指定模型
	 * @param string $model 模型名
	 * @param string $module 模块名
	 * @return boolean
	 */
	private function loadModel($model, $module) {
		$module_path = !empty($module) ? $module . DS : '';
		$model_path = APP_PATH . $module_path . 'Model' . DS . $model . '.php';
		if (!file_exists($model_path)) {
			Log::record(Log::WARNING, "{$model_path} not found!");
			return new Model;
		}
		Framework::loadModule($module);
		return new $model;
	}
	
	/*
	 * 重新设置所需要执行的事件
	 * @param string $doing 活动名（可选，默认为空）
	 */
	public function setDoing($doing='') {
		$this->_doing = $doing;
	}
	
	/**
	 * 获取用户请求事件，存在不合法字符时返回false
	 * @param string $prefix 前缀
	 * @return string
	 */
	protected function getDoing($prefix='') {
		$do = $this->get(static::DO_KEY);
		if (empty($do)) {
			return 'doing';
		}
		if (!preg_match("/^[0-9a-zA-Z]+$/", $do)) {
			Log::record(Log::ILLEGAL, 'Detected request key[do]: '.$do);
			return false;
		}
		return $prefix . strtolower($do);
	}
	
	/*
	 * 安全沙箱
	 */
	protected function sandBox() {
		$_GET && filter_data($_GET);
		$_POST && filter_data($_POST);
		$_COOKIE && filter_data($_COOKIE);
	}
	
}
