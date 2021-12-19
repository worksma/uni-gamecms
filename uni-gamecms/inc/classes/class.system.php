<?PHP
	class System {
		private $secondary;

		public function __construct() {
			$this->secondary = pdo()->query("SELECT * FROM `config__secondary` LIMIT 1")->fetch(PDO::FETCH_OBJ);
		}

		public function secondary() {
			return $this->secondary;
		}

		/*
			Получаем API сервер
		*/
		public function server($location = null) {
			$url = pdo()
			->query("SELECT * FROM `config__updates` WHERE `id`='" . configs()->update_server . "' LIMIT 1")
			->fetch(PDO::FETCH_OBJ)
			->url;

			return isset($location) ? ("https://" . $url) : $url;
		}

		/*
			Проверяем наличие обновления
		*/
		public function is_update() {
			$result = curl($this->server(true), json_encode([
				'module' => 'uni-gamecms',
				'type' => 'is_update',
				'version' => $this->secondary->version
			]));

			return json_decode($result)->alert;
		}

		/*
			Проверка на валидность сайта
		*/
		public function is_site_valid($domain) {
			if(!filter_var($domain, FILTER_VALIDATE_URL)):
				return false;
			endif;

			$ch = curl_init($domain);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);

			if($result):
				return true;
			endif;

			return false;
		}

		/*
			Получение ссылки на обновление
		*/
		public function link_update() {
			$result = curl($this->server(true), json_encode([
				'module' => 'uni-gamecms',
				'type' => 'link_update',
				'version' => $this->secondary->version,
				'domain' => $_SERVER['SERVER_NAME']
			]));

			return $result;
		}

		/*
			Получение информации о новой версии
		*/
		public function get_version_description() {
			$result = curl($this->server(true), json_encode([
				"module" => "uni-gamecms",
				"type" => "desc_update",
				"version" => $this->secondary->version,
				"domain" => $_SERVER['SERVER_NAME']
			]));

			return json_decode($result)->message;
		}

		/*
			Загрузка файлов
		*/
		public function download($temp, $file, $url) {
			$dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $temp;
			if(!file_exists($dir)):
				mkdir($dir);
			endif;

			$file = "$dir/$file";
			$f = fopen($file, "wb");

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_FILE, $f);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);

			return (object)[
				'compiled' => fclose($f),
				'file' => $file
			];
		}

		/*
			Удаление дирекции с файлами
		*/
		public function removeDirs($dir) {
			$files = array_diff(scandir($dir), ['.', '..']);

			foreach($files as $file):
				if(is_dir("$dir/$file")):
					$this->removeDirs("$dir/$file");
				else:
					unlink("$dir/$file");
				endif;
			endforeach;

			rmdir($dir);
		}
	}