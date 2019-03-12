<?php

require LIB_PATH . 'Email/PHPMailer.php';
require LIB_PATH . 'Email/Smtp.php';

class Email {
	
	private static $mail;
	
	private static function init() {
		self::$mail = new PHPMailer();
		self::$mail->IsSMTP();
		self::$mail->IsHTML(true);
		self::$mail->SMTPDebug = 0; // 启用SMTP调试信息（测试） 1 =错误和消息 2 =消息
		self::$mail->SMTPAuth = true; // 开启认证
		self::$mail->Encoding = 'base64';
		$config = config::system('email');
		self::$mail->Host = $config['host']; // SMTP服务器地址
		if ($config['ssl']) {
			self::$mail->SMTPSecure = 'ssl'; //设置使用ssl方式连接
			self::$mail->SMTPOptions['ssl'] = array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			);
		}
		self::$mail->Port = $config['port']; // SMTP服务器端口
		self::$mail->Username = $config['account']; // SMTP服务器帐号
		self::$mail->Password = $config['password']; // SMTP服务器密码
		self::$mail->SetFrom($config['account'], $config['name']); //发件人地址
		self::$mail->AddReplyTo($config['account'], $config['name']);
	}
	
	public static function send($email, $subject, $content, $charset = 'utf8') {
		if (empty(self::$mail)) {
			self::init();
		}
		self::$mail->CharSet = $charset; //设置邮件的字符编码
		self::$mail->Subject = $subject; //邮件主题
		self::$mail->Body = $content; //邮件内容
		self::$mail->AddAddress($email); //收件人邮箱和姓名
		if (!self::$mail->Send()) {
			return false;
		} else {
			return true;
		}
	}
	
}
