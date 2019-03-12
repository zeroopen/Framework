<?php

use OSS\OssClient;
use OSS\Core\OssException;

class Aliyun {
	
	private $aliyun;
	
	/**
	 * 上传文件根目录
	 * @var string
	 */
	private $rootPath;
	
	/**
	 * 上传错误信息
	 * @var string
	 */
	private $error = ''; //上传错误信息
	
	private $config = array(
		'endpoint' => '', //阿里云oss地址
		'accessKeyId' => '', //阿里云api帐号
		'accessKeySecret' => '', //阿里云api密码
		'bucket' => '', //空间名称
		'timeout' => 300 //超时时间
	);
	
	/**
	 * 构造函数，用于设置上传根路径
	 * @param array  $config 配置
	 */
	public function __construct($config) {
		$this->config = array_merge($this->config, $config);
		$accessKeyId = $config['accessKeyId'];
		$accessKeySecret = $config['accessKeySecret'];
		$endpoint = $config['endpoint'];
		$isCName = isset($config['isCName']) ? $config['isCName'] : false;
		$securityToken = isset($config['securityToken']) ? $config['securityToken'] : NULL;
		$this->loadSdk();
		try {
			$this->aliyun = new OssClient($accessKeyId, $accessKeySecret, $endpoint, $isCName, $securityToken);
		} catch (OssException $e) {
			Log::record(Log::ERROR, $e->getMessage());
		}
	}
	
	/**
	 * 检测上传根目录
	 * @param string $rootpath   根目录
	 * @return boolean true-检测通过，false-检测失败
	 */
	public function checkRootPath($rootpath) {
		$this->rootPath = trim($rootpath, './') . '/';
		return true;
	}
	
	/**
	 * 检测上传目录
	 * @param  string $savepath 上传目录
	 * @return boolean          检测结果，true-通过，false-失败
	 */
	public function checkSavePath($savepath) {
		return true;
	}
	
	/**
	 * 保存指定文件
	 * @param  array   $file    保存的文件信息
	 * @param  boolean $replace 同名文件是否覆盖
	 * @return boolean          保存状态，true-成功，false-失败
	 */
	public function save($file, $replace = true) {
		$key = $this->rootPath . $file['savepath'] . $file['savename'];
		$content = file_get_contents($file['tmp_name']);
		try {
			$result = $this->aliyun->putObject($this->config['bucket'], $key, $content, false);
		} catch (OssException $e) {
			Log::record(Log::ERROR, 'putObject: FAILED;' . $e->getMessage());
			return false;
		}
		return false === $result ? false : true;
	}
	
	/**
	 * 获取文件所在网址
	 * @return string 地址
	 */
	public function getUrl() {
		return $this->config['bucket'] . '.' . $this->config['endpoint'];
	}
	
	/**
	 * 创建目录
	 * @param  string $savepath 要创建的目录
	 * @return boolean          创建状态，true-成功，false-失败
	 */
	public function mkdir($savepath) {
		return true;
	}
	
	/**
	 * 获取最后一次上传错误信息
	 * @return string 错误信息
	 */
	public function getError() {
		return $this->error;
	}
	
	/**
	 * 加载SDK包
	 */
	public function loadSdk() {
		require dirname(__FILE__) . '/Aliyun/AliyunOss.phar';
	}
	
}
