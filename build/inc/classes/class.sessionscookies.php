<?php
class SessionsCookies {
	public $browser;
	public $ip = 0;
	public $admin_ip = 0;
	private $salt = '';
	private $lifetime;
	private $path = '/'; // Cookie path
	private $domain = '';
	private $secure = false; // Use HTTPS
	private $httponly = true; // Use HTTP-only cookie

	function __construct($salt, $domain) {
		if(empty($_SERVER['HTTP_USER_AGENT'])) {
			$_SERVER['HTTP_USER_AGENT'] = 'undefined';
		}

		$this->salt = $salt;
		$this->domain = $domain;
		$this->browser = substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
		$this->lifetime = time()+60*60*24*30;

		@session_set_cookie_params(0, $this->path, $this->domain, $this->secure, $this->httponly);
		@session_start();
	}

	public function get_cache($password) {
		if($this->ip) {
			return md5($password.$this->salt.$this->ip.md5($this->browser));
		} else {
			return md5($this->salt.$password.md5($this->browser));
		}
	}

	public function get_admin_cache($password) {
		if($this->admin_ip) {
			return md5($this->admin_ip.$password.$this->salt.md5($this->browser));
		} else {
			return md5($password.$this->salt.md5($this->browser));
		}
	}

	public function unset_user_session() {
		if(empty($_SESSION['admin'])) {
			session_unset();
			session_destroy();
			$_SESSION = array();

			$this->unset_user_cookie();
		} else {
			unset($_SESSION['id']);
			unset($_SESSION['cache']);
			unset($_SESSION['login']);
			unset($_SESSION['rights']);
			unset($_SESSION['stickers']);

			$this->unset_user_cookie();
		}
	}

	public function unset_admin_session() {
		unset($_SESSION['admin']);
		unset($_SESSION['admin_cache']);
		unset($_SESSION['dev_mode']);
	}

	public function clean_user_session() {
		unset($_SESSION['id']);
		unset($_SESSION['cache']);
		unset($_SESSION['login']);
		unset($_SESSION['rights']);
		unset($_SESSION['stickers']);
	}

	public function clean_admin_session() {
		unset($_SESSION['admin']);
		unset($_SESSION['admin_cache']);
		unset($_SESSION['dev_mode']);
	}

	public function set_cookie($var, $val) {
		setcookie($var, $val, $this->lifetime, $this->path ,$this->domain, $this->secure, $this->httponly);
		if($val == "") {
			unset($_COOKIE[$var]);
		}
	}

	public function set_user_cookie() {
		$this->set_cookie("cache", $_SESSION['cache']);
		$this->set_cookie("id", $_SESSION['id']);
	}

	public function unset_user_cookie() {
		$this->set_cookie("cache", "");
		$this->set_cookie("id", "");
	}

	public function set_token() {
		if(empty($_SESSION['token'])){
			$token = md5($this->salt.uniqid(mt_rand().microtime()));
			$_SESSION['token'] = $token;
		} else {
			$token = $_SESSION['token'];
		}
		return $token;
	}
}