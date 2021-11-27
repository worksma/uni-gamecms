<?PHP
	class Prefixes {
		private $pdo = null;
		var $temp = "";

		public function __construct($pdo = null) {
			if(empty($pdo)):
				return false;
			endif;

			$this->pdo = $pdo;
		}

		public function get_server($id_server = null) {
			return $this->pdo->query("SELECT * FROM `servers` WHERE `id`='$id_server' LIMIT 1")->fetch(PDO::FETCH_OBJ);
		}

		public function get_servers() {
			$sth = $this->pdo->query("SELECT * FROM `servers` WHERE 1");

			if($sth->rowCount()):
				$servers = "<option selected disabled>Выберите сервер.</option>";

				while($row = $sth->fetch(PDO::FETCH_OBJ)):
					$servers .= "<option value=\"$row->id\">$row->name - $row->address</option>";
				endwhile;

				return $servers;
			endif;

			return "<option selected disabled>Серверов нет.</option>";
		}

		public function get_term($id_server = null) {
			$sth = $this->pdo->query("SELECT * FROM `servers__prefixes_term` WHERE `id_server`='$id_server'");

			if($sth->rowCount()):
				$term = "<option selected disabled>Выберите тариф.</option>";

				while($row = $sth->fetch(PDO::FETCH_OBJ)):
					$term .= "<option value=\"$row->id\">" . (($row->time == 0) ? "Навсегда" : ($row->time . " дня(ей)")) . ' - ' . $this->get_term_price($row->id) . " руб.". (empty($row->discount) ? "" : (" (скидка {$row->discount}%)")) ."</option>";
				endwhile;

				return $term;
			endif;

			return "<option selected disabled>Сервер не настроен.</option>";
		}

		public function term($id_term = null) {
			if(empty($id_term)):
				return null;
			endif;

			return $this->pdo->query("SELECT * FROM `servers__prefixes_term` WHERE `id`='$id_term'")->fetch(PDO::FETCH_OBJ);
		}

		public function setPrefix($data = []) {
			$this
			->pdo
			->prepare("INSERT INTO `servers__prefixes`(`id_server`, `id_user`, `steamid`, `nickname`, `password`, `prefix`, `date_start`, `date_end`) VALUES (:id_server, :id_user, :steamid, :nickname, :password, :prefix, :date_start, :date_end)")
			->execute([
				':id_server' => (isset($data->id_server) ? $data->id_server : 0),
				':id_user' => (isset($data->id_user) ? $data->id_user : 0),
				':steamid' => (isset($data->steamid) ? $data->steamid : 'none'),
				':nickname' => (isset($data->nickname) ? $data->nickname : 'none'),
				':password' => (isset($data->password) ? $data->password : 'none'),
				':prefix' => (isset($data->prefix) ? $data->prefix : 'none'),
				':date_start' => date("Y.m.d H:i:s"),
				':date_end' => (($this->term($data->term)->time == 0) ? '0000.00.00 00:00:00' : date("Y.m.d H:i:s", strtotime("+{$this->term($data->term)->time} days")))
			]);

			return true;
		}

		public function get_term_price($id_term = null) {
			if(empty($id_term)):
				return null;
			endif;

			$term = $this->term($id_term);

			if(empty($term->discount)):
				return $term->price;
			endif;

			return ($term->price - ($term->price/100*$term->discount));
		}

		public function getTemp($file = null) {
			if(empty($file)):
				return null;
			endif;

			if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $file)):
				$this->temp = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $file);
			else:
				$this->temp = "[Файл: $file, не был найден!]";
			endif;

			return $this;
		}

		public function addTemp($file = null) {
			if(empty($file)):
				return null;
			endif;

			if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $file)):
				$this->temp .= file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $file);
			else:
				$this->temp .= "[Файл: $file, не был найден!]";
			endif;

			return $this;
		}

		public function setTemp($who = null, $to = null) {
			$this->temp = str_replace($who, $to, $this->temp);
			return $this;
		}

		public function endTemp() {
			return $this->temp;
		}

		public function clearTemp() {
			$this->temp = "";
			return $this;
		}

		public function addTerm($data = []) {
			$this
			->pdo
			->prepare("INSERT INTO `servers__prefixes_term`(`id_server`, `price`, `time`, `discount`, `rcon`) VALUES (:id_server, :price, :time, :discount, :rcon)")
			->execute([
				':id_server' => $data->id_server,
				':price' => $data->price,
				':time' => $data->time,
				':discount' => $data->discount,
				':rcon' => $data->rcon
			]);

			return $this;
		}

		public function editTerm($data = []) {
			$this
			->pdo
			->prepare("UPDATE `servers__prefixes_term` SET `price`=:price,`time`=:time,`discount`=:discount,`rcon`=:rcon WHERE `id`=:id")
			->execute([
				':id' => $data->index,
				':price' => $data->price,
				':time' => $data->time,
				':discount' => $data->discount,
				':rcon' => $data->rcon
			]);

			return $this;
		}

		public function is_speech($id_server = null, $prefix = null) {
			if(empty($id_server) || empty($prefix)):
				return true;
			endif;

			$sth = $this
			->pdo
			->query("SELECT * FROM `servers__prefixes_ban` WHERE `id_server`='$id_server'");

			if(!$sth->rowCount()):
				return false;
			endif;

			while($row = $sth->fetch(PDO::FETCH_OBJ)):
				if(strripos($prefix, $row->speech) === false):
					continue;
				else:
					return true;
				endif;
			endwhile;

			return false;
		}

		public function addSpeech($data = []) {
			$this
			->pdo
			->prepare("INSERT INTO `servers__prefixes_ban`(`id_server`, `speech`) VALUES (:id_server, :speech)")
			->execute([
				':id_server' => $data->id_server,
				':speech' => $data->speech
			]);

			return $this;
		}

		public function editSpeech($data = []) {
			$this
			->pdo
			->prepare("UPDATE `servers__prefixes_ban` SET `speech`=:speech WHERE `id`=:id")
			->execute([
				':id' => $data->index,
				':speech' => $data->speech
			]);

			return $this;
		}

		public function user_prefixes($id_user = null) {
			if(empty($id_user)):
				return null;
			endif;

			$sth = $this->pdo->query("SELECT * FROM `servers__prefixes` WHERE `id_user`='$id_user'");

			if(!$sth->rowCount()):
				return "<tr class='text-center'><td colspan='4'>Префиксов нет.</td></tr>";
			endif;

			$this->clearTemp();
			global $conf;

			$i = 0;
			while($row = $sth->fetch(PDO::FETCH_OBJ)):
				$i++;

				$this
				->addTemp("templates/$conf->template/tpl/elements/prefixes.tpl")
				->setTemp("{nickname}", $row->nickname)
				->setTemp("{id_server}", $this->get_server($row->id_server)->name)
				->setTemp("{prefix}", $row->prefix)
				->setTemp("{i}", $i);
			endwhile;

			return $this->endTemp();
		}
	}