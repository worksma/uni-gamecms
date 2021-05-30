<?PHP
	class Playground {
		private $pdo, $conf;
		var $elbuf;
		var $configs;
		
		public function __construct($pdo = null, $conf = null) {
			if(isset($pdo)) {
				$this->pdo = $pdo;
			}
			
			if(isset($conf)) {
				$this->conf = $conf;
			}
			
			$ath = $pdo->query("SELECT * FROM `playground` WHERE `id`='1'");
			$ath->setFetchMode(PDO::FETCH_OBJ);
			$this->configs = $ath->fetch();
		}
		
		public function get_resource_active($id_category, $id_user) {
			$sth = $this->pdo->query("SELECT * FROM `playground__purchases` WHERE `id_category`='{$id_category}' and `id_user`='{$id_user}' and `active`='1'");
			$sth->setFetchMode(PDO::FETCH_OBJ);
			
			$sth = $this->pdo->query("SELECT * FROM `playground__product` WHERE `id`='{$sth->fetch()->id_product}'");
			$sth->setFetchMode(PDO::FETCH_OBJ);
			
			return $sth->fetch()->resource;
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
		
		public function clear_element() {
			$this->elbuf = "";
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