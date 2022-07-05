<?php

class PagesInfo
{
	const PROFILE_PAGE_URL = 'profile';

	public $full_host;
	private $full_url = null;
	private $originalUrl = '';

	private function getUrl()
	{
		$url = '';

		if(isset($_SERVER["PATH_INFO"]) && !empty($_SERVER["PATH_INFO"])) {
			$this->originalUrl = clean($_SERVER["PATH_INFO"], null);
			$url = $this->originalUrl;
		}

		if(isset($_SERVER["REQUEST_URI"]) && !empty($_SERVER["REQUEST_URI"])) {
			$this->originalUrl = clean($_SERVER["REQUEST_URI"], null);
			$url = preg_replace('/\?.*/', '', $this->originalUrl);
		}
		
		$this->full_url = trim($this->originalUrl, '/');

		return trim($url, '/');
	}

	private function isUserPage($url)
	{
		$urlParts = explode('/', $url);

		if($urlParts[0] == self::PROFILE_PAGE_URL) {
			return true;
		} else {
			return false;
		}
	}

	public function page_info($url = null)
	{
		if(empty($url)) {
			$url = clean($this->getUrl());
			
			if(substr($url, -5) == 'index') {
				unset($_GET["/$url"]);
				
				if(!isset($_GET) || !is_array($_GET) || count($_GET) == 0) {
					header("Location: /" . trim(substr($url, 0, -5), '/'));
					http_response_code(301);
					die;
				}
				else {
					header("Location: /" . trim(substr($url, 0, -6) . strstr($_SERVER['REQUEST_URI'], '?')));
					http_response_code(301);
					die;
				}
			}
		}

		$originalUrl = $url;

		if($this->isUserPage($url)) {
			$url = self::PROFILE_PAGE_URL;
		}

		$STH = pdo()->prepare("SELECT * FROM pages WHERE url=:url AND active='1' LIMIT 1");
		$STH->execute([':url' => $url]);
		$row = $STH->fetch(PDO::FETCH_OBJ);
		if(empty($row->id)) {
			if(substr_count($url, '/') > 1) {
				show_error_page('wrong_url');
			}

			$STH = pdo()->query("SELECT * FROM pages WHERE url='error_page' LIMIT 1");
			$row = $STH->fetch(PDO::FETCH_OBJ);
			if(empty($row->id)) {
				exit('[Class PagesInfo]: Page not found');
			}
		}

		if(substr($this->originalUrl, -1) == '/' && $row->page == 1) {
			header('Location: ../../' . $url);
			http_response_code(301);
			die;
		}

		if(!file_exists($_SERVER["DOCUMENT_ROOT"] . '/' . $row->file)) {
			exit('[Class PagesInfo]: File of module not found');
		}

		$row->kind        = $this->setKind($row->kind);
		$row->image       = $this->full_host . $row->image;
		$row->robots      = $this->setRobots($row->robots);
		$row->full_url    = $this->full_host . $this->full_url;
		$row->originalUrl = $originalUrl;

		if($row->privacy != 0 && $row->privacy != 1 && $row->privacy != 2) {
			$row->privacy = 1;
		}

		return $row;
	}

	public function to_nav($name, $point = 0, $id = 0, $second_name = '')
	{
		$STH = pdo()->prepare("SELECT id, title, url FROM pages WHERE name=:name LIMIT 1");
		$STH->execute([':name' => $name]);
		$row = $STH->fetch(PDO::FETCH_OBJ);
		if(empty($row->id)) {
			$array[0] = '../';
			$array[1] = 'none';
		} else {
			$array[0] = '../' . $row->url;
			$array[1] = $row->title;
		}
		if($point == 1) {
			$array[0] = 'active';
		}
		if($id != 0) {
			$array[0] .= '?id=' . $id;
		}
		if($second_name != '') {
			$array[1] = $second_name;
		}

		return $array;
	}

	public function compile_str($str, $value)
	{
		return str_replace("{value}", $value, $str);
	}

	public function compile_keywords($str)
	{
		return str_replace(',,', ',', str_replace(' ', ',', $str));
	}

	public function compile_img_str($img)
	{
		return $this->full_host . $img;
	}

	private function setKind($kind)
	{
		switch($kind) {
			case '2':
				return 'article';
			case '3':
				return 'profile';
			default:
				return 'website';
		}
	}

	private function setRobots($robots)
	{
		switch($robots) {
			case '2':
				return 'none';
			default:
				return 'all';
		}
	}
}
