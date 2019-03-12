<?php

class IndexController extends Controller {
	
	const MODEL = 'IndexModel';
	
	public function index() {
		$step = $this->get('step');
		if (!empty($step)) {
			if (!file_exists(SELF_PATH . 'verify.lock')) {
				$step = 1;
			}
			$method = 'step_' . $step;
			$this->view->assgin('step', $step);
			if (method_exists($this, $method)) {
				$this->$method();
			}
		} else {
			$this->view->assgin('step', 0);
			$this->view->display('index');
		}
	}
	
	public function step_1() {
		$this->view->display('step1');
	}
	
	public function step_2() {
		$next = version_compare(PHP_VERSION, '5.3', '>=');
		$php = PHP_VERSION . ($next ? '' : '（版本过低）');
		$context['mysqli扩展'] = extension_loaded('mysqli') ? true : false;
		$context['iconv/mb_string扩展'] = (extension_loaded('iconv') && extension_loaded('mbstring')) ? true : false;
		$context['zlib扩展'] = extension_loaded('zlib') ? true : false;
		$context['json扩展'] = extension_loaded('json') ? true : false;
		$context['gd扩展'] = extension_loaded('gd') ? (
			(function_exists('imagepng') && function_exists('imagejpeg') && function_exists('imagegif')) ? true : false
		) : false;
		$context['mcrypt扩展'] = extension_loaded('mcrypt') ? true : false;
		$context['curl扩展'] = extension_loaded('curl') ? true : false;
		$context['short_open_tag'] = ini_get('short_open_tag') ? true : false;
		if ($next) {
			foreach($context as $value) {
				if (!$value) {
					$next = false;
					break;
				}
			}
		}
		$this->view->assgin('php', $php);
		$this->view->assgin('context', $context);
		$this->view->assgin('next', $next);
		$this->view->display('step2');
	}
	
	public function step_3() {
		$path = array(
			//'网站根目录' => SELF_PATH,
			'framework/data/' => FRAMEWORK.'data/',
			'framework/data/backup/' => FRAMEWORK.'data/backup/',
			'framework/data/cache/' => FRAMEWORK.'data/cache/',
			'framework/data/log/' => FRAMEWORK.'data/log/',
			'framework/config/' => CONF_PATH,
			//'upload/' => SELF_PATH.'upload/',
			//'upload/avatar/' => SELF_PATH.'upload/avatar/',
			//'upload/img/' => SELF_PATH.'upload/img/'
		);
		if (!is_dir(FRAMEWORK . 'data/cache')) {
			mkdir(FRAMEWORK . 'data/cache');
		}
		$next = true;
		$dirs = $this->model->checkPermission($path, $next);
		$this->view->assgin('dirs', $dirs);
		$this->view->assgin('next', $next);
		$this->view->display('step3');
	}
	
	public function step_4() {
		$this->view->display('step4');
	}
	
	public function step_5() {
		$key = $this->get('key');
		$ok = false;
		if (!empty($key) && !empty($_SESSION['ok'])) {
			if ($key == $_SESSION['ok']) {
				File::create(DATA_PATH . 'install.lock');
				File::delete(SELF_PATH . 'verify.lock');
				session(null);
				$ok = true;
			}
		}
		$this->view->assgin('ok', $ok);
		$this->view->display('step5');
	}
	
}
