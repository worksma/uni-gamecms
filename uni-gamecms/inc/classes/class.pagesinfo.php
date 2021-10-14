<?php

class PagesInfo
{
	const PROFILE_PAGE_URL = 'profile';

	public $full_host;
	private $full_url = null;
	private $pdo;

	function __construct($pdo)
	{
		if(!isset($pdo)) {
			exit('[Class PagesInfo]: No connection to the database');
		}
		$this->pdo = $pdo;
	}

	private function get_url()
	{
		if(isset($_SERVER["PATH_INFO"]) && !empty($_SERVER["PATH_INFO"])) {
			$this->full_url = trim($_SERVER["PATH_INFO"], "/");
			$url_info       = trim($_SERVER["PATH_INFO"], "/");
			if(substr($url_info, -5) == 'index') {
				$url_info = trim(substr($url_info, 0, -5), "/");

				if(empty($_GET)) {
					header('Location: ../' . $url_info);
					http_response_code(301);
					die;
				}
			}
		} else {
			unset($url_info);
		}

		if(isset($_SERVER["REQUEST_URI"]) && !empty($_SERVER["REQUEST_URI"])) {
			$this->full_url = trim($_SERVER["REQUEST_URI"], "/");
			$url_info2      = trim(
				preg_replace('/\?.*/', '', $_SERVER["REQUEST_URI"]),
				"/"
			);
			if(substr($url_info2, -5) == 'index') {
				$url_info2 = trim(substr($url_info2, 0, -5), "/");

				if(empty($_GET)) {
					header('Location: ../' . $url_info2);
					http_response_code(301);
					die;
				}
			}

		} else {
			unset($url_info2);
		}

		if(isset($url_info)) {
			$url = $url_info;
		} elseif(isset($url_info2)) {
			$url = $url_info2;
		}
		if(empty($url)) {
			$url = '';
		}

		return $url;
	}

	private function isUserPage($url) {
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
			$originalUrl = clean($this->get_url(), null);
			$url = $originalUrl;
		} else {
			$originalUrl = $url;
		}

		if($this->isUserPage($url)) {
			$url = self::PROFILE_PAGE_URL;
		}

		$STH = $this->pdo->prepare(
			"SELECT * FROM pages WHERE url=:url AND active='1' LIMIT 1"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':url' => $url]);
		$row = $STH->fetch();
		if(empty($row->id)) {
			if(substr_count($url, '/') > 1) {
				show_error_page('wrong_url');
			}

			$STH = $this->pdo->query(
				"SELECT * FROM pages WHERE url='error_page' LIMIT 1"
			);
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			if(empty($row->id)) {
				exit('[Class PagesInfo]: Page not found');
			}
		}
		if(!file_exists($_SERVER["DOCUMENT_ROOT"] . '/' . $row->file)) {
			exit('[Class PagesInfo]: File of module not found');
		}

		$row->kind        = $this->set_kind($row->kind);
		$row->image       = $this->full_host . $row->image;
		$row->robots      = $this->set_robots($row->robots);
		$row->full_url    = $this->full_host . $this->full_url;
		$row->originalUrl = $originalUrl;

		if($row->privacy != 0 && $row->privacy != 1 && $row->privacy != 2) {
			$row->privacy = 1;
		}

		return $row;
	}

	private function set_kind($kind)
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

	private function set_robots($robots)
	{
		switch($robots) {
			case '2':
				return 'none';
			default:
				return 'all';
		}
	}

	public function to_nav($name, $point = 0, $id = 0, $second_name = '')
	{
		$STH = $this->pdo->prepare(
			"SELECT id, title, url FROM pages WHERE name=:name LIMIT 1"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':name' => $name]);
		$row = $STH->fetch();
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
}
