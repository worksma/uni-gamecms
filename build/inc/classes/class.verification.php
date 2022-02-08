<?PHP
	class Verification {
		private $pdo;
		
		public function __construct($pdo = null) {
			if(isset($pdo)) {
				$this->pdo = $pdo;
			}
		}
		
		public function is_very($user_id = null, $check = 1) {
			if(isset($user_id)) {
				$a = $this->f_pdo("SELECT * FROM `users` WHERE `id`='{$user_id}' and `verification`='{$check}'");
				
				if($a[0]->rowCount()) {
					return true;
				}
			}
			
			return false;
		}
		
		public function get_very_style($template = null) {
			if(isset($template)) {
				return file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/templates/{$template}/tpl/elements/verification.tpl");
			}
			
			return file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/templates/standart/tpl/elements/verification.tpl");
		}
		
		public function set_very($user_id, $value) {
			return $this->pdo->query("UPDATE `users` SET `verification`='{$value}' WHERE `id`='{$user_id}'");
		}
		
		public function send_very($user_id) {
			return $this->pdo->query("UPDATE `users` SET `verification`='2' WHERE `id`='{$user_id}'");
		}
		
		public function f_pdo($query = null) {
			if(isset($query)) {
				$sth = $this->pdo->query($query);
				$sth->setFetchMode(PDO::FETCH_OBJ);
				
				return [$sth, $sth->fetch()];
			}
			
			return false;
		}
		
		public function admin_request_verifications() {
			$sth = $this->pdo->query("SELECT * FROM `users` WHERE `verification`='2'");
			$a = "";
			
			if($sth->rowCount()) {
				$sth->setFetchMode(PDO::FETCH_OBJ);
				
				while($row = $sth->fetch()) {
					$a .= "<tr>";
						$a .= "<td>{$row->login}</td>";
						$a .= "<td>" . date("d.m.Y в H:i", strtotime($row->regdate)) . "</td>";
						$a .= "<td><span class=\"text-success\" OnClick=\"send_very({$row->id});\" style=\"cursor:pointer;\">Одобрить</span> |
						<span class=\"text-danger\" OnClick=\"send_not_very({$row->id});\" style=\"cursor:pointer;\">Отказать</span>";
					$a .= "</tr>";
				}
			}
			else {
				$a = "<tr class=\"text-center\">
					<td colspan=\"4\">Заявок нет</td>
				</tr>";
			}
			
			return $a;
		}
	}