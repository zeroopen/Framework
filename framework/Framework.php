<?php

class Framework {
	
	// 是否已启动框架
	private static $started = false;
	
	// 模块数组
	private static $module = array(
		'state' => true, // 模块状态，是否开启多模块功能
		'list' => array(), // 允许通过url加载的模块集
		'default' => null, // 默认加载的模块
		'now' => null, // 稍后加载的模块
		'loaded' => array() // 已加载的模块
	);
	
	// 允许加载的App类型
	private static $load_type = array('Controller', 'Model', 'Driver');
	
	// 已加载的App类
	private static $loaded_class = array();
	
	private static $isset_mobile = false;
	
	/**
	 * 启动框架（定义系统常量，初始化环境变量，并载入常用文件）
	 */
	public static function start() {
		if (self::$started) {
			return;
		}
		self::_config();
		self::_load();
		self::_init();
		self::_define();
		self::$started = true;
	}
	
	/**
	 * 检查框架是否启动
	 */
	private static function _check() {
		if (!self::$started) {
			if (defined('DEBUG') && DEBUG) {
				echo 'Framework not started!';
			}
			exit;
		}
	}
	
	private static function _config() {
		defined('DEBUG') or define('DEBUG', false);
		defined('LOG') or define('LOG', true);
		defined('DS') or define('DS', DIRECTORY_SEPARATOR);
		defined('FRAMEWORK') or define('FRAMEWORK', dirname(__FILE__) . DS);
		defined('CONF_PATH') or define('CONF_PATH', FRAMEWORK . 'config' . DS);
		defined('CORE_PATH') or define('CORE_PATH', FRAMEWORK . 'core' . DS);
		defined('APP_PATH') or define('APP_PATH', FRAMEWORK . 'application' . DS);
		defined('LIB_PATH') or define('LIB_PATH', FRAMEWORK . 'library' . DS);
		defined('DATA_PATH') or define('DATA_PATH', FRAMEWORK . 'data' . DS);
		defined('INSTALL') or define('INSTALL', file_exists(DATA_PATH . 'install.lock'));
		if (DEBUG) {
			error_reporting(E_ALL);
			ini_set('display_errors', '1');
		} else {
			error_reporting(0);
			ini_set('display_errors', '0');
		}
	}
	
	private static function _load() {
		require FRAMEWORK . 'Helper.php'; //助手函数
		require LIB_PATH . 'Config.php'; //系统配置类
		require LIB_PATH . 'Cache.php'; //缓存类
		require LIB_PATH . 'Db.php'; //数据库操作类
		require LIB_PATH . 'Encrypt.php'; //加密类
		require LIB_PATH . 'Url.php'; //url类
		require LIB_PATH . 'Log.php'; //日志类
		require LIB_PATH . 'File.php'; //文件类
		require LIB_PATH . 'Random.php'; //随机类
		require CORE_PATH . 'Controller.php'; //控制器核心类
		require CORE_PATH . 'Model.php'; //模型核心类
		require CORE_PATH . 'View.php'; //模板核心类
	}
	
	private static function _init() {
		if (INSTALL && !ini_get('short_open_tag')) {
			Log::record(Log::ERROR, 'System is not compatible, please open the PHP short tag!');
			exit;
		}
		if (LOG) {
			set_error_handler(array(Log::class, 'error'));
			set_exception_handler(array(Log::class, 'exception'));
			register_shutdown_function(array(Log::class, 'lastError'));
		}
		spl_autoload_register(array(__CLASS__, '_autoload'));
		!session_id() && session_start();
		config::load('site');
		config::load('system');
		date_default_timezone_set(config::system('timezone'));
		ini_set('default_charset', config::system('charset'));
		ini_set('magic_quotes_runtime', '0');
		config::system('gzip') && ob_start('ob_gzip');
		if (empty($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['SCRIPT_FILENAME'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
		}
		if (empty($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['PATH_TRANSLATED'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
		}
		if (!config::system('urlrewrite')) {
			$_SERVER['REQUEST_URI'] = (substr($_SERVER['PHP_SELF'], -9) == 'index.php') ? substr($_SERVER['PHP_SELF'], 0, -9) : $_SERVER['PHP_SELF'];
			if (!isset($_SERVER['QUERY_STRING']) || empty($_SERVER['QUERY_STRING'])) {
				$_SERVER['QUERY_STRING'] = '';
			} else {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
	}
	
	private static function _define() {
		defined('IS_MOBILE') or define('IS_MOBILE', is_mobile());
		defined('IS_AJAX') or define('IS_AJAX', is_ajax());
		defined('IS_CLIENT') or define('IS_CLIENT', is_client());
		defined('IS_DOING') or define('IS_DOING', (IS_AJAX or IS_CLIENT));
		defined('__SELF__') or define('__SELF__', str_replace(ROOT_PATH, '', SELF_PATH));
		defined('__TIME__') or define('__TIME__', time());
		defined('__NAME__') or define('__NAME__', config::site('name'));
		defined('__LOGO__') or define('__LOGO__', config::site(is_mobile()?'mobile':'default', 'logo'));
		defined('__STATIC__') or define('__STATIC__', '/' . config::system('static', 'url_path'));
		defined('URL_REWRITE') or define('URL_REWRITE', config::system('urlrewrite'));
		defined('PAGESIZE') or define('PAGESIZE', config::system('pagesize'));
		defined('REQUEST_URI') or define('REQUEST_URI', $_SERVER['REQUEST_URI']);
	}
	
	private static function _autoload($class) {
		self::_check();
		$type = null;
		foreach (self::$load_type as $value) {
			if (strpos($class, $value) !== false) {
				$type = $value;
				break;
			}
		}
		if (!empty($type)) {
			if (self::moduleState()) {
				$module = empty(self::$module['now']) ? MODULE : self::$module['now'];
				require APP_PATH . $module . DS . $type . DS . $class . '.php';
			    self::$loaded_class[$module][$type][] = $class;
				self::$module['now'] = null;
			} else {
				require APP_PATH . $type . DS . $class . '.php';
				self::$loaded_class[$type][] = $class;
			}
		}
	}
	
	/**
	 * 开启手机版
	 */
	public static function openMobile() {
		self::_check();
		if (config::site('mobile', 'status') == 1 && IS_MOBILE) {
			$url = config::site('mobile', 'url');
			if ($url != '' && $_SERVER['HTTP_HOST'] != $url) {
				header('location: ' . $url, true, 301);
				exit;
			}
		}
		self::$isset_mobile = true;
	}
	
	/**
	 * 设置允许通过url载入的模块集与默认模块
	 * @param array|string $module 载入的模块集，array形式为多模块载入，string形式为单模块载入，单模块载入忽略模块
	 * @param string $default 默认载入的模块
	 */
	public static function module($module, $default=null) {
		self::_check();
		if (is_string($module)) {
			self::$module['list'] = array();
			if (!empty($module) && is_string($module)) {
				self::$module['default'] = $module;
				self::$module['state'] = false;
			}
		} else if (is_array($module)) {
			self::$module['list'] = $module;
			if (!empty($default) && is_string($default)) {
				self::$module['default'] = $default;
			} else {
				self::$module['default'] = null;
			}
			self::$module['state'] = true;
		}
	}
	
	/**
	 * 自动根据url获得模块名与活动名，并返回活动实例
	 * @param string $default 默认载入的活动
	 * @return object
	 */
	public static function getObject($default='') {
		self::_check();
		$array = Url::parse();
		if (self::moduleState()) {
			if (empty($array['module']) && !empty(self::$module['default'])) {
				$array['module'] = self::$module['default'];
			} else {
				if ($array['module'] == self::$module['default']) {
					if (empty($array['action']) || $array['action'] == $default) {
						return false;
					}
				}
			}
			if (!in_array($array['module'], self::$module['list'])) {
				return false;
			}
			empty($default) && $default = $array['module'];
		} else {
			$array['module'] = null;
		}
		if (empty($array['action'])) {
			$array['action'] = $default;
		} else {
			if (empty($default) && $array['action'] == $default) {
				return false;
			}
		}
		return self::open($array['module'], $array['action']);
	}
	
	/**
	 * 打开一个活动
	 * @param string $module 模块名
	 * @param string $action 活动名
	 * @return object
	 */
	public static function open($module, $action) {
		self::_check();
		if (defined('RUNNING')) {
			Log::record(Log::ILLEGAL, 'Action has been loaded, not allowed to call again!');
			exit;
		}
		if (empty($action)) {
			return false;
		}
		$controller_name = $action . 'Controller';
		if (is_null($module)) {
			$controller_path = APP_PATH . 'Controller' . DS . $controller_name . '.php';
		} else {
			$controller_path = APP_PATH . $module . DS . 'Controller' . DS . $controller_name . '.php';
		}
		if (!file_exists($controller_path)) {
			return false;
		}
		define('MODULE', $module);
		define('ACTION', $action);
		define('RUNNING', true);
		return new $controller_name();
	}
	
	/**
	 * 运行活动
	 * @param object $object 活动对象
	 * @return boolean
	 */
	public static function run($object) {
		self::_check();
		if (is_object($object) && method_exists($object, 'run')) {
			if (IS_DOING) {
				return $object->run('do_');
			}
			return $object->run();
		}
		return false;
	}
	
	public static function moduleState() {
		return self::$module['state'];
	}
	
	/*
	 * 加载指定模块
	 * @param string $module 模块名
	 */
	public static function loadModule($module) {
		self::_check();
		if (self::moduleState() && file_exists(APP_PATH . $module)) {
			self::$module['now'] = $module;
			self::$module['loaded'][] = $module;
		}
	}
	
	public static function getLoadedClass() {
		self::_check();
		return self::$loaded_class;
	}
	
	/**
	 * 获取页面风格
	 * @return string
	 */
	public static function getView() {
		self::_check();
		$view = config::site('default', 'view');
		$mobile = config::site('mobile');
		if (self::$isset_mobile && $mobile['status']) {
			if (($mobile['url'] != '' && $_SERVER['HTTP_HOST'] == $mobile['url']) || IS_MOBILE) {
				if (self::moduleState()) {
					$path = APP_PATH . MODULE . DS . 'View' . DS . $mobile['view'] . DS;
				} else {
					$path = APP_PATH . 'View' . DS . $mobile['view'] . DS;
				}
				if (file_exists($path)) {
					$view = $mobile['view'];
				}
			}
		}
		return ucfirst(strtolower($view));
	}
	
}