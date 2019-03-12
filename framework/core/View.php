<?php

class View {
	
	// 实例对象
	protected static $_instance = null;
	
	protected $_variables = array();
	protected $_action;
	
	private final function __construct() {}
	
	protected final function __clone() {}
	
	public static function getInstance() {
		if (static::$_instance === null) {
			static::$_instance = new static();
		}
		static::$_instance->_initialize();
		return static::$_instance;
	}
	
	protected function _initialize() {
		defined('__VIEW__') or define('__VIEW__', Framework::getView());
	}
	
	/*
	 * 获取视图路径
	 * @return string
	 */
	protected function getViewPath($path) {
		return $path . '/' . strtolower(__VIEW__) . '/';
	}
	
	/*
	 * 对视图进行赋值操作
	 * @param string $name 名字
	 * @param mixed $value 值
	 */
	public final function assgin($name, $value) {
		if (is_var($name)) {
			$this->_variables[$name] = $value;
		}
	}
	
	/*
	 * 显示指定视图
	 * @param string $action 活动
	 */
	public final function display($action) {
		if ($this->_action) {//禁止多次加载及防止内部调用
			return false;
		}
		$this->_action = $action;
		$this->load($action);
	}
	
	/*
	 * 加载模板
	 * @param string $view 视图文件
	 * @param array $params 传入参数（仅此视图）
	 */
	protected final function load($view, $params=array()) {
		if ($this->parse($view)) {
			unset($view);
			extract($this->_variables);
			if (is_array($params) && count($params)>0) {
				$var = array();
				foreach ($params as $key => $value) {
					if (is_var($value)) {
						$var[$key] = $value;
					}
				}
				extract($var);
			}
			unset($params);
			include $this->_viewpath;
		}
	}
	
	/*
	 * 解析参数获取视图路径
	 * @param string $view 视图
	 * @return boolean
	 */
	protected function parse($view) {
		if (strpos($view, '/') !== false) {
			$array = explode('/', $view);
			$module = $array[0];
			$view = $array[1];
		} else {
			$module = MODULE;
		}
		if (!empty($module)) {
			$module .= DS;
		}
		if (!is_null(__VIEW__)) {
			$view = __VIEW__ . DS . $view;
		}
		$this->_viewpath = APP_PATH . $module . DS . 'View' . DS . $view . '.html';
		if (!file_exists($this->_viewpath)) {
			return false;
		}
		return true;
	}
	
}
