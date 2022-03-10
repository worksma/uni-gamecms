<?PHP
	class Trading {
		public static function getProducts($page = 1, $category = null) {
			$category = clean($category);
			
			if(isset($category)) {
				if(self::IsValidCategory($category)) {
					$ic = self::GetCategoryId($category);
				}
				else {
					$ic = -1;
				}
			}
			
			$limit = self::conf()->limit_product;
			$start = ($page * $limit) - $limit;
			
			$sth = pdo()->query("SELECT * FROM `playground__product` ".(empty($category) ? "" : "WHERE `id_category`='$ic'")." ORDER BY `id` DESC LIMIT $start, $limit");
			
			if(!$sth->rowCount()) {
				return '<center>Товаров нет.</center>';
			}
			
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				trading()
				->load_element("ui/product")
				->set_element("{id}", $row->id)
				->set_element("{name}", $row->name)
				->set_element("{price}", $row->price)
				->set_element("{resource}", $row->resource)
				->set_element("{availability}", $row->availability)
				->set_element("{id_category}", $row->id_category);
			}
			
			return trading()->show_element();
		}
		
		public static function rowProducts($category = null) {
			$category = clean($category);
			
			if(empty($category)) {
				return pdo()->query("SELECT * FROM `playground__product` WHERE 1")->rowCount();
			}
			
			return pdo()->query("SELECT * FROM `playground__product` WHERE `id_category`='" . self::GetCategoryId($category) . "'")->rowCount();
		}
		
		public static function IsValidCategory($name) {
			$name = clean($name);
			return pdo()->query("SELECT * FROM `playground__category` WHERE `code_name`='$name' LIMIT 1")->rowCount();
		}
		
		public static function GetCategoryId($name) {
			$name = clean($name);
			
			$sth = pdo()->query("SELECT * FROM `playground__category` WHERE `code_name`='$name' LIMIT 1");
			
			if($sth->rowCount()) {
				return $sth->fetch(PDO::FETCH_OBJ)->id;
			}
			
			return null;
		}
		
		public static function conf() {
			return pdo()->query("SELECT * FROM `playground` LIMIT 1")->fetch(PDO::FETCH_OBJ);
		}
		
		public static function getCategoryMenu($active = 0) {
			$sth = pdo()->query("SELECT * FROM `playground__category` WHERE 1");
			
			$buf = "<li><a href=\"/market\" " . ($active == 0 ? "class='active'" : "") . ">Все товары</a></li>";
			if(!$sth->rowCount()) {
				return $buf;
			}
			
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$buf .= "<li><a href=\"/market?sort=" . $row->code_name . "\" " . ($active == $row->id ? "class='active'" : "") . ">" . $row->name . "</a></li>";
			}
			
			return $buf;
		}
		
		public static function IsValidProduct($pid) {
			return pdo()->query("SELECT * FROM `playground__product` WHERE `id`='$pid' LIMIT 1")->rowCount();
		}
		
		public static function GetProduct($pid) {
			return pdo()->query("SELECT * FROM `playground__product` WHERE `id`='$pid' LIMIT 1")->fetch(PDO::FETCH_OBJ);
		}
		
		public static function SetProduct($pid, $key, $vault) {
			return pdo()->prepare("UPDATE `playground__product` SET `$key`=:vault WHERE `id`=:pid")->execute([
				':pid' => $pid,
				':vault' => $vault
			]);
		}
		
		public static function GetBalance($uid) {
			return pdo()->query("SELECT * FROM `users` WHERE `id`='$uid' LIMIT 1")->fetch(PDO::FETCH_OBJ)->playground;
		}
		
		public static function SetBalance($uid, $count) {
			return pdo()->prepare("UPDATE `users` SET `playground`=:count WHERE `id`=:uid LIMIT 1")->execute([
				':uid' => $uid,
				':count' => $count
			]);
		}
		
		public static function addPurchases($uid, $pid, $price) {
			$product = self::GetProduct($pid);
			return pdo()->prepare("INSERT INTO `playground__purchases`(`pid`, `category`, `uid`, `price`, `date`, `enable`) VALUES (:pid, :category, :uid, :price, :date, :enable)")->execute([
				':pid' => $pid,
				':uid' => $uid,
				':category' => $product->id_category,
				':price' => $price,
				':date' => date("Y-m-d H:i:s"),
				':enable' => '0'
			]);
		}
		
		public static function GetInventory($uid) {
			$sth = pdo()->query("SELECT * FROM `playground__purchases` WHERE `uid`='$uid' ORDER BY `id` DESC");
			
			if(!$sth->rowCount()) {
				return "<div class='col'><center>Инвентарь пуст</center></div>";
			}
			
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$Product = Trading::GetProduct($row->pid);
				
				trading()
				->load_element("card/items")
				->set_element("{id}", $row->id)
				->set_element("{name}", $Product->name)
				->set_element("{category}", $row->category)
				->set_element("{enable}", $row->enable ? "active" : "")
				->set_element("{resource}", $Product->resource);
			}
			
			return trading()->show_element();
		}
		
		public static function IsUserPurchases($uid, $pid) {
			return pdo()->query("SELECT * FROM `playground__purchases` WHERE `uid`='$uid' and `id`='$pid' LIMIT 1")->rowCount();
		}
		
		public static function GetPurchases($pid) {
			return pdo()->query("SELECT * FROM `playground__purchases` WHERE `id`='$pid' LIMIT 1")->fetch(PDO::FETCH_OBJ);
		}
		
		public static function SetPurchases($pid, $key, $value) {
			return pdo()->prepare("UPDATE `playground__purchases` SET `$key`=:value WHERE `id`=:pid LIMIT 1")->execute([
				':value' => $value,
				':pid' => $pid
			]);
		}
		
		public static function OffUserPurchases($uid, $category) {
			return pdo()->query("UPDATE `playground__purchases` SET `enable`='0' WHERE `uid`='$uid' and `category`='$category'");
		}
		
		public static function RemoteNoty($pid, $postfields = []) {
			$Product = self::GetProduct($pid);
			
			if(!$Product->executor && $Product->executor == 'none') {
				return false;
			}
			
			$postfields = json_encode([
				'secret' => self::conf()->secret,
				'time' => time(),
				'object' => json_encode($postfields)
			]);
			
			$_headers = stream_context_create([
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false
				],
				'http' => [
					'method' => 'POST',
					'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
					'content' => $postfields
				]
			]);
			
			return @file_get_contents($Product->executor, false, $_headers);
		}
		
		public static function getServersRcon() {
			$sth = pdo()->query("SELECT * FROM `servers` WHERE `rcon`='1'");
			
			$temp = "<option disabled selected>Выберите сервер</option>";
			
			if(!$sth->rowCount()) {
				return $temp;
			}
			
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$temp .= "<option value=\"" . $row->id . "\">" . $row->name . "</option>";
			}
			
			return $temp;
		}
		
		public static function getServer($sid) {
			return pdo()->query("SELECT * FROM `servers` WHERE `id`='1' LIMIT 1")->fetch(PDO::FETCH_OBJ);
		}
		
		public static function addCommand($pid, $sid, $command) {
			return pdo()->prepare("INSERT INTO `playground__commands`(`pid`, `sid`, `command`) VALUES (:pid, :sid, :command)")->execute([
				':pid' => $pid, ':sid' => $sid, ':command' => htmlspecialchars($command)
			]);
		}
		
		public static function getCommands($pid) {
			$pid = clean($pid, "int");
			$sth = pdo()->query("SELECT * FROM `playground__commands` WHERE `pid`='$pid' ORDER BY `id` DESC");
			
			if(!$sth->rowCount()) {
				return "<tr><td style='width:100%;'><center>Список пуст</center></td><td></td></tr>";
			}
			
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				trading()
				->load_element("table/rcon", true)
				->set_element("{id}", $row->id)
				->set_element("{server}", self::getServer($row->sid)->name)
				->set_element("{command}", htmlspecialchars_decode($row->command));
			}
			
			return trading()->show_element();
		}
		
		public static function removeCommand($id) {
			return pdo()->query("DELETE FROM `playground__commands` WHERE `id`='$id' LIMIT 1");
		}
		
		public static function rowCommands($pid) {
			return pdo()->query("SELECT * FROM `playground__commands` WHERE `pid`='$pid'")->rowCount();
		}
		
		public static function addLogs($text) {
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/trading.txt', date("[d.m.y H:i:s] ") . $text, FILE_APPEND);
		}
		
		public static function SendRcon($pid, $arr = []) {
			$sth = pdo()->query("SELECT * FROM `playground__commands` WHERE `pid`='$pid' ORDER BY `id` DESC");
			
			if(!$sth->rowCount()) {
				return false;
			}
			
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$server = self::getServer($row->sid);
				
				$command = str_replace("{uid}", $arr['uid'], $row->command);
				$command = str_replace("{steamid}", $arr['steamid'], $command);
				$command = str_replace("{pid}", $arr['pid'], $command);
				$command = str_replace("{price}", $arr['price'], $command);
				
				try {
					SourceQuery()
					->Connect($server->ip, $server->port, 1, (($server->game == 'Counter-Strike: 1.6') ? SourceQuery::GOLDSOURCE : SourceQuery::SOURCE));
					SourceQuery()->SetRconPassword($server->rcon_password);
					SourceQuery()->Rcon(htmlspecialchars_decode($command));
				}
				catch(Exception $e) {
					self::addLogs($e->getMessage());
					return false;
				}
			}
			
			return true;
		}
	}