<?PHP
	class Levels {
		private $pdo;
		
		public function __construct($pdo = null) {
			if(empty($pdo)):
				$this->pdo = pdo();
			else:
				$this->pdo = $pdo;
			endif;
		}
		
		public function get_user_level($id = null) {
			if(empty($id)) {
				return false;
			}
			
			$sth = $this->pdo->query("SELECT * FROM `users` WHERE `id`='{$id}'");
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$row = $sth->fetch();
			
			return $row->level;
		}
		
		public function set_user_level($id = null, $level = 0) {
			if(empty($id)) {
				return false;
			}
			
			return $this->pdo->query("UPDATE `users` SET `level`='{$level}' WHERE `id`='{$id}'");
		}
		
		public function get_user_exp($id = null) {
			if(empty($id)) {
				return false;
			}
			
			$sth = $this->pdo->query("SELECT * FROM `users` WHERE `id`='{$id}'");
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$row = $sth->fetch();
			
			return $row->experience;
		}
		
		public function set_user_exp($id = null, $exp = 0) {
			if(empty($id)) {
				return false;
			}
			
			return $this->pdo->query("UPDATE `users` SET `experience`='{$exp}' WHERE `id`='{$id}'");
		}
		
		public function next_level_data($level) {
			if(!$this->is_maximum($level)) {
				$sth = $this->pdo->query("SELECT * FROM `levels__profile` WHERE `level`='".($level + 1)."' LIMIT 1");
				$sth->setFetchMode(PDO::FETCH_OBJ);
				$row = $sth->fetch();
				
				return (object)[
					'level' => $row->level,
					'exp' => $row->experience,
					'name' => $row->name
				];
			}
			
			return (object)['level' => $level, 'exp' => $this->get_info_level($level)->exp, 'name' => $this->get_info_level($level)->name];
		}
		
		public function get_info_level($level) {
			$sth = $this->pdo->query("SELECT * FROM `levels__profile` WHERE `level`='{$level}' LIMIT 1");
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$row = $sth->fetch();
				
			return (object)[
				'level' => $row->level,
				'exp' => $row->experience,
				'name' => $row->name
			];
		}
		
		public function is_maximum($level) {
			$sth = $this->pdo->query("SELECT * FROM `levels__profile` WHERE 1 ORDER BY level DESC LIMIT 1");
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$row = $sth->fetch();
			
			if($level < $row->level) {
				return false;
			}
			
			return true;
		}
		
		public function add_user_exp($id = null, $exp = 0) {
			if(empty($id)) {
				return false;
			}
			
			if($this->pdo->query("UPDATE `users` SET `experience`='".($this->get_user_exp($id) + $exp)."' WHERE `id`='{$id}'")) {
				$this->write_levels($id);
			}
		}
		
		public function write_levels($id = null) {
			if(empty($id)) {
				return false;
			}
			
			if($this->is_maximum($this->get_user_level($id))) {
				return false;
			}
			
			if($this->get_user_exp($id) >= $this->next_level_data($this->get_user_level($id))->exp && $this->get_user_level($id) < $this->next_level_data($this->get_user_level($id))->level) {
				$this->set_user_level($id, $this->get_user_level($id) + 1);
				$this->write_levels($id);
			}
		}

		public function default_configs() {
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/ajax/addons/levels/configs.json', json_encode([
				'send_new_comments'	=> '20',
				'add_topic'			=> '20',
				'send_answer'		=> '20',
				'send_user_comment'	=> '20',
				'chat_send_message'	=> '3'
			]));
		}

		public function configs() {
			$configs = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/ajax/addons/levels/configs.json');
			return json_decode($configs, true);
		}
	}