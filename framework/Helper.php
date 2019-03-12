<?php

/**
 * session操作函数
 * @param int|string $key session键名
 * @param mixed $value session键值（如果不传此值则返回键值，键名不存在时返回null）
 */
function session($key, $value = '') {
	if (is_null($key)) {
		session_unset();
		session_destroy();
	} else if ($value === '') {
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	} else if (is_null($value)) {
		unset($_SESSION[$key]);
	} else {
		$_SESSION[$key] = $value;
	}
}

/**
 * 循环执行一个函数，如果此函数返回false将继续循环，否则结束
 * @param callable $callback 回调函数
 * @param array $params 被传入回调函数的参数数组
 * @param int $max 最大循环次数，为0则无限制
 * @return mixed
 */
function loop($callback, $params = array(), $max = 0) {
	$max = (int) $max;
	if (!is_callable($callback) || !is_array($params) || $max < 0) {
		return false;
	}
	$result = false;
	$i = 0;
	while ($result === false) {
		if ($max > 0 && $i == $max) {
			break;
		}
		$result = call_user_func_array($callback, $params);
		$i++;
	}
	return $result;
}

if (!function_exists('getallheaders')) {
	function getallheaders() {
		$out = array();
		foreach($_SERVER as $key => $value) {
			if (substr($key, 0, 5) == "HTTP_") {
				$key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
				$out[$key] = $value;
			}
		}
		return $out;
	}
}

/**
 * 检查是否是ajax请求
 * @return boolean
 */
function is_ajax() {
	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		return true;
	}
	return false;
}

/*
 * 检查是否是客户端请求
 * @return boolean
 */
function is_client() {
	if (!empty($_SERVER['HTTP_X_APP_CLIENT']) && strtolower($_SERVER['HTTP_X_APP_CLIENT']) == config::client('sign')) {
		return true;
	}
	return false;
}

/**
 * 检查是否是移动设备访问
 * @return boolean
 */
function is_mobile() {
	if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
		return true;
	}
	if (isset($_SERVER['HTTP_VIA'])) {
		return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
	}
	$regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
	$regex_match .= "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|meizu|miui|ucweb";
	$regex_match .= "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
	$regex_match .= "symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
	$regex_match .= "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";
	$regex_match .= ")/i";
	if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']))) {
		return true;
	}
	return false;
}

/**
 * 检查当前模块（及当前活动）是否与参数相同
 * @param string $module 模块名
 * @param string $action 活动名（可选）
 * @return boolean
 */
function is_index($module, $action = null) {
	if ($module != MODULE) {
		return false;
	}
	if (!is_null($action) && $action != ACTION) {
		return false;
	}
	return true;
}

/**
 * 检查是否是合法变量名
 * 规定合法变量名为：字母或下划线开头，后跟字母、数字、下划线、短杠、其它。
 * 不能使用汉字等双字节字符。
 * @param string $var
 * @return boolean
*/
function is_var($var) {
	return !!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $var);
}

/**
 * 检查数组是否是关联数组
 * @param array $array
 * @return boolean
 */
function is_assoc($array) {
	if (!is_array($array)) {
		return false;
	}
	return array_keys($array) !== range(0, count($array) - 1);
}

function ob_gzip($content) {
	if (!headers_sent() && // 如果页面头部信息还没有输出
		extension_loaded("zlib") && // 而且zlib扩展已经加载到PHP中
		strstr($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") // 而且浏览器说它可以接受GZIP的页面
	) {
		$content = gzencode($content, 8); //用zlib提供的gzencode()函数执行级别为9的压缩，这个参数值范围是0-9，0表示无压缩，9表示最大压缩，当然压缩程度越高越费CPU。
		// 然后用header()函数给浏览器发送一些头部信息，告诉浏览器这个页面已经用GZIP压缩过了！
		header("Content-Encoding: gzip");
		header("Vary: Accept-Encoding");
		header("Content-Length: " . strlen($content));
	}
	return $content; //返回压缩的内容，或者说把压缩好的饼干送回工作台。
}

/**
 * 将下划线命名转换为驼峰命名
 * @param string $string
 * @param boolean $ucfirst 是否转换为首字母大写
 * @return string
 */
function to_camelize($string, $ucfirst = true) {
	$string = ucwords(str_replace('_', ' ', $string));
	$string = str_replace(' ','',lcfirst($string));
	return $ucfirst ? ucfirst($string) : $string;
}

/**
 * 数量转汉字
 * @param int $number 数字
 * @param int $precision 保留小数位数（默认为2）
 * @return string （$number不是数字或数字字符串将返回false）
 */
function number_cn($number, $precision = 2) {
	if (is_numeric($number)) {
		$ten_thousand = '10000';
		$hundred_million = '100000000';
		if ($number > $ten_thousand) {
			if ($number < $hundred_million) {
				return round($number/$ten_thousand, $precision).'万';
			} else {
				return round($number/$hundred_million, $precision).'亿';
			}
		}
		return $number;
	}
	return false;
}

/**
 * 转换时间戳为距离现在日期文字时间
 * @param int $time 时间戳
 * @param int $now 相对时间戳（比如：现在）
 * @return string
 */
function time_cn($time, $now = __TIME__) {
	$time_lag = $now - $time;
	if ($time_lag < 86400) {
		if ($time_lag < 3600) {
			if ($time_lag < 60) {
				return '刚刚';
			}
			return ceil($time_lag/60) . '分钟前';
		}
		return ceil($time_lag/3600) . '小时前';
	}
	if (date('Ym', $time) >= date('Ym', $now)-12) {
		$past_date = date('Ymd', $time);
		$now_date = date('Ymd', $now);
		if ($past_date >= $now_date-30) {
			if ($past_date == $now_date-1) {
				return '昨天';
			}
			if ($past_date == $now_date-2) {
				return '前天';
			}
			return $now_date - $past_date . '天前';
		}
		return date('Ym', $now) - date('Ym', $time) . '个月前';
	}
	return date('Y-m-d', $time);
}

/**
 * 获取微秒级时间戳
 */
function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float) $usec + (float) $sec) * 1000;
}

/**
 * 获取远程文件或数据（注意：本函数依赖curl扩展）
 * 示例：$content = get_url($url, function($curl) {}, array());
 * @param string $url 链接
 * @param callable $callback 回调函数（此函数必须有一个可传递参数，保留：curl对象）
 * @param array $array 回调传参
 * @return string
 */
function get_url($url, $callback = null, $array = array()) {
	$curl = curl_init();
	if (stripos($url, 'https://') === 0) {
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_SSLVERSION,1); //CURL_SSLVERSION_TLSv1
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	if (is_callable($callback)) {
		call_user_func_array($callback, array_merge(array($curl), $array));
	}
	$content = curl_exec($curl);
	curl_close($curl);
	return $content;
}

/*
 * 安全过滤器
 * @param mixed $data 数据
 */
function filter_data(&$data) {
	if (is_array($data)) {
		foreach ($data as $key => $value) {
			filter_data($data[$key]);
		}
	} else if (is_string($data)) {
		if (!get_magic_quotes_gpc()) {
			$data = addslashes($data);
		}
		$rule = array('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/','/script/','/javascript/','/vbscript/','/expression/','/applet/','/meta/','/xml/','/blink/','/link/','/style/','/embed/','/object/','/frame/','/layer/','/title/','/bgsound/','/base/','/onload/','/onunload/','/onchange/','/onsubmit/','/onreset/','/onselect/','/onblur/','/onfocus/','/onabort/','/onkeydown/','/onkeypress/','/onkeyup/','/onclick/','/ondblclick/','/onmousedown/','/onmousemove/','/onmouseout/','/onmouseover/','/onmouseup/','/onunload/');
		$data = preg_replace($rule, '', $data);
		$data = htmlentities(strip_tags($data), ENT_QUOTES, 'UTF-8');
	}
}

/**
 * 在字符串指定位置插入指定字符串
 * @param string $string 字符串
 * @param int $i 插入位置
 * @param string $substr 待插入的字符串
 */
function str_insert($string, $i, $substr) {
	return substr_replace($string, $substr, $i, 0);
}

//非法词组
function filter_word($key=null) {
	//恶意及犯罪类
	$data[1] = array(
		'帮手', '杀手', '凶手', '打手', '刺客', '人质',
		'杀人', '抢劫', '诈骗', '绑架', '勒索', '害人',
		'杀死', '勒死', '吊死', '淹死', '炸死', '电死',
		'打死', '弄死', '劈死', '砍死', '压死', '轧死',
		'扎死', '捅死', '刺死', '碾死', '毒死', '掐死',
		'下毒', '下药', '打人', '群殴', '调戏', '猥亵',
		'强奸', '轮奸', '嫖娼', '赌博', '赌场', '吸毒',
		'卖肾', '卖肝', '报仇', '复仇', '仇恨', '打架'
	);
	//严禁物品
	$data[2] = array(
		'枪支', '弹药', '子弹', '猎枪', '匕首', '武器',
		'炸弹', '手雷', '手枪', '雷管', '机枪', '步枪',
		'毒品', '鸦片', '海洛因', '吗啡', '大麻', '可卡因',
		'可待因', '那可汀', '盐酸二氢埃托啡',
		'冰毒', '甲基苯丙胺', '海洛因', 'K粉', '咖啡因',
		'三唑仑', '海乐神', '酣乐欣', '淡蓝色片', '迷药',
		'蒙汗药', '迷魂药', '安纳咖', '氟硝安定',
		'麦角乙二胺', '安眠酮', '丁丙诺啡', '地西泮',
		'氰化钾', '苯三酚', '麻黄素', '氰化钠', '硫酸铊',
	);
	//脏话类
	$data[3] = array(
		'傻子', '傻逼', '傻比', '傻叉', '煞笔', '煞比',
		'妈逼', '你妈', '你爸', '你妹', '你爷爷', '你奶奶',
		'你祖宗', '草泥马', '神经病', '猪狗不如', '禽兽不如',
		'贱', 'nmb', 'NMB', 'cnm', 'CNM', 'sb', 'SB',
		'妈了个逼', '马勒戈壁', '滚蛋', '他妈的', '妈的',
		'我草', '我操', '我靠', '我艹', '卧槽', '我擦'
	);
	//淫秽色情类
	$data[4] = array(
		'裸聊', '裸照', '裸体', '公关', '鸡婆', '找小姐',
		'开房', '上床', '包养', '奶罩', '胸罩', '内裤',
		'奶子', '阴茎', '鸡巴', '屁眼', '发情', '发春',
		'打飞机', '骚逼', '插逼', '操逼', 'AV', 'av',
		'A片', 'H片', '色片', '成人电影'
	);
	$array = $key != null ? $data[$key] : $data;
	return $array;
}