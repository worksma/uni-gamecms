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

		/*
			Работа с мониторингом
		*/
		public function sc_game($game) {
			switch($game):
				case "Counter-Strike: 1.6":
					return SourceQuery::GOLDSOURCE;
				break;

				default:
					return SourceQuery::SOURCE;
				break;
			endswitch;
		}

		public function players_monitoring($remote = false, $query = null, $host = "https://analytics.worksma.ru/players.php") {
			if($remote):
				$players = curl($host, json_encode([
					'domain'		=> $_SERVER['SERVER_NAME'],
					'key'			=> self::secondary()->mon_key,
					'server'		=> $query
				]));

				$players = json_decode($players, true);
				return $players;
			endif;

			$sc = SourceQuery();
			if(empty($sc)):
				$sc = new SourceQuery;
			endif;

			try {
				$sc->Connect($query['ip'], $query['port'], 1, self::sc_game($query['game']));
				$players = $sc->GetPlayers();
				$sc->Disconnect();
			}
			catch(Exception $e) {
				log_error("Ошибка мониторинга: " . $e->getMessage());
				$players = 0;
			}

			return $players;
		}

		public function update_monitoring($remote = null, $host = "https://analytics.worksma.ru") {
			pdo()->exec("DELETE FROM `monitoring` WHERE 1");

			$sth = pdo()->query("SELECT * FROM `servers` WHERE `show`='1' ORDER BY `trim`");

			if(!$sth->rowCount()):
				return false;
			endif;

			if(isset($remote)):
				$dServers = json_encode([
					'domain'		=> $_SERVER['SERVER_NAME'],
					'key'			=> self::secondary()->mon_key,
					'servers'		=> $sth->fetchAll()
				]);

				$servers = curl($host, $dServers);
				$servers = json_decode($servers, true);

				foreach($servers as $server):
					if(strpos($server['Map'], "/") !== false):
						$server['Map'] = explode("/", $server['Map']);
						$server['Map'] = end($server['Map']);
					endif;

					pdo()
					->prepare("INSERT INTO `monitoring`(`ip`, `port`, `name`, `address`, `game`, `players_now`, `players_max`, `map`, `type`, `sid`) VALUES (:ip, :port, :name, :address, :game, :players_now, :players_max, :map, :type, :sid)")
					->execute([
						':ip'			=> $server['ip'],
						':port'			=> $server['port'],
						':name'			=> $server['HostName'],
						':address'		=> $server['address'],
						':game'			=> $server['game'],
						':players_now'	=> $server['Players'],
						':players_max'	=> $server['MaxPlayers'],
						':map'			=> $server['Map'],
						':type'			=> $server['type'],
						':sid'			=> $server['id']
					]);
				endforeach;
				pdo()->prepare("UPDATE `config__secondary` SET `mon_time`=:mon_time WHERE `id`='1' LIMIT 1")->execute([':mon_time' => time()]);

				return true;
			endif;

			$sc = SourceQuery();
			if(empty($sc)):
				$sc = new SourceQuery;
			endif;

			while($row = $sth->fetch(PDO::FETCH_OBJ)):
				try {
					$sc->Connect($row->ip, $row->port, 1, self::sc_game($row->game));
					$data = $sc->GetInfo();
					$sc->Disconnect();

					if(strpos($data['Map'], "/") !== false):
						$data['Map'] = explode("/", $data['Map']);
						$data['Map'] = end($data['Map']);
					endif;

					pdo()
					->prepare("INSERT INTO `monitoring`(`ip`, `port`, `name`, `address`, `game`, `players_now`, `players_max`, `map`, `type`, `sid`) VALUES (:ip, :port, :name, :address, :game, :players_now, :players_max, :map, :type, :sid)")
					->execute([
						':ip'			=> $row->ip,
						':port'			=> $row->port,
						':name'			=> $data['HostName'],
						':address'		=> $row->address,
						':game'			=> $row->game,
						':players_now'	=> $data['Players'],
						':players_max'	=> $data['MaxPlayers'],
						':map'			=> $data['Map'],
						':type'			=> $row->type,
						':sid'			=> $row->id
					]);
				}
				catch(Exception $e) {
					log_error("Ошибка мониторинга: " . $e->getMessage());

					pdo()
					->prepare("INSERT INTO `monitoring`(`ip`, `port`, `name`, `address`, `game`, `players_now`, `players_max`, `map`, `type`, `sid`) VALUES (:ip, :port, :name, :address, :game, :players_now, :players_max, :map, :type, :sid)")
					->execute([
						':ip'			=> $row->ip,
						':port'			=> $row->port,
						':name'			=> '0',
						':address'		=> $row->address,
						':game'			=> $row->game,
						':players_now'	=> '0',
						':players_max'	=> '0',
						':map'			=> '0',
						':type'			=> $row->type,
						':sid'			=> $row->id
					]);
				}
			endwhile;

			pdo()->prepare("UPDATE `config__secondary` SET `mon_time`=:mon_time WHERE `id`='1' LIMIT 1")->execute([':mon_time' => time()]);

			return true;
		}
		
		public function currency() {
			return json_decode(pdo()->query("SELECT * FROM `config` LIMIT 1")->fetch(PDO::FETCH_OBJ)->currency);
		}
	}