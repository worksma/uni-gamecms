<?PHP
	class Playground {
		private $pdo, $conf;
		var $elbuf;
		var $configs;
		
		public function __construct($pdo = null, $conf = null) {
			if(empty($pdo)):
				return false;
			endif;
			
			$this->pdo = $pdo;
			
			if(isset($conf)) {
				$this->conf = $conf;
			}
			
			$ath = $pdo->query("SELECT * FROM `playground` WHERE `id`='1'");
			$ath->setFetchMode(PDO::FETCH_OBJ);
			$this->configs = $ath->fetch();
		}
		
		public function is_bonuses() {
			if($this->get_configs()->bonuses > 0):
				return true;
			endif;

			return false;
		}

		public function add_bonuses($id_user = null, $rub = null) {
			if(empty($id_user) || empty($rub)):
				return null;
			endif;
			
			$bonuses = round($rub/100*$this->get_configs()->bonuses);

			include_once($_SERVER['DOCUMENT_ROOT'] . '/inc/notifications.php');
			send_noty($this->pdo, "Вы получили: бонус " . $bonuses . ' ' . $this->get_configs()->currency . ' за пополнение баланса на ' . $rub . ' руб.', $id_user, 1);

			return $this->add_balance($id_user, $bonuses);
		}

		public function get_resource_active($id_category, $id_user) {
			$sth = $this->pdo->query("SELECT * FROM `playground__purchases` WHERE `id_category`='{$id_category}' and `id_user`='{$id_user}' and `active`='1'");
			$sth->setFetchMode(PDO::FETCH_OBJ);
				
			if($sth->rowCount()):
				$sth = $this->pdo->query("SELECT * FROM `playground__product` WHERE `id`='{$sth->fetch()->id_product}'");
				$sth->setFetchMode(PDO::FETCH_OBJ);
				
				return $sth->fetch()->resource;
			else:
				return null;
			endif;
		}
		
		public function get_configs() {
			return $this->configs;
		}
		
		public function get_balance($id_user) {
			$sth = $this->pdo->query("SELECT `playground` FROM `users` WHERE `id`='{$id_user}'");
			$sth->setFetchMode(PDO::FETCH_OBJ);
			
			return $sth->fetch()->playground;
		}
		
		public function set_balance($id_user, $value) {
			return $this->pdo->query("UPDATE `users` SET `playground`='{$value}' WHERE `id`='{$id_user}'");
		}
		
		public function add_balance($id_user, $value) {
			$value = ($this->get_balance($id_user) + $value);
			return $this->pdo->query("UPDATE `users` SET `playground`='{$value}' WHERE `id`='{$id_user}'");
		}
		
		public function min_balance($id_user, $value) {
			$value = ($this->get_balance($id_user) - $value);
			return $this->pdo->query("UPDATE `users` SET `playground`='{$value}' WHERE `id`='{$id_user}'");
		}
		
		public function get_category($name = null) {
			return $this->pdo->query("SELECT * FROM `playground__category` WHERE `code_name`='{$name}'")->fetch(PDO::FETCH_OBJ);
		}

		public function clear_element() {
			$this->elbuf = "";
		}
		
		public function notification($address = null, $data = []) {
			if(empty($address)):
				return false;
			endif;

			$postfields = json_encode([
				'secret' => $this->configs->secret,
				'time' => time(), 'data' => json_encode($data)
			]);

			$headers = stream_context_create([
				'ssl' => ['verify_peer' => false, 'verify_peer_name' => false], 'http' => ['method' => 'POST','header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,'content' => $postfields]]);
			return file_get_contents('https://' . $address, false, $headers);
		}

		public function is_secret($key = null) {
			if(empty($key))
				return false;

			if($key == $this->configs->secret)
				return true;

			return false;
		}

		public function load_element($name, $admin = null) {
			if(isset($admin)) {
				$patch = $_SERVER['DOCUMENT_ROOT'] . "/templates/admin/tpl/elements/playground/{$name}.tpl";
			}
			else {
				$patch = $_SERVER['DOCUMENT_ROOT'] . "/templates/{$this->conf->template}/tpl/elements/playground/{$name}.tpl";
			}
			
			if(!file_exists($patch)) {
				$this->elbuf .= "Ошибка загрузки шаблона: " . $patch;
				return;
			}
			
			$this->elbuf .= file_get_contents($patch);
		}
		
		public function set_element($search, $to) {
			$this->elbuf = str_replace($search, $to, $this->elbuf);
			return true;
		}
		
		public function show_element() {
			return $this->elbuf;
		}
	}