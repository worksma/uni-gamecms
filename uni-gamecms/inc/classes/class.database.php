<?PHP
	class Database {
		private $injector;
		
		public function __construct($db) {
			$this->injector = new mysqli($db['host'], $db['user'], $db['password'], $db['name']);
			
			if(mysqli_connect_errno()) {
				exit("Не удалось подключиться к БД! Ошибка: " . mysqli_connect_error() . "");
			}
			
			$this->injector->set_charset("utf8");
		}
		
		public function query($query) {
			return $this->injector->query($query);
		}
		
		public function m_query($query) {
			return $this->injector->multi_query($query);
		}
		
		public function rows($query) {
			return @mysqli_num_rows($query);
		}
		
		public function arr($query) {
			return @mysqli_fetch_array($query, MYSQLI_ASSOC);
		}
		
		public function frow($query) {
			return @mysqli_fetch_row($query);
		}
		
		public function assoc($query) {
			return @mysqli_fetch_assoc($query);
		}
		
		public function fqr($data = []) {
			$q = $this->injector->query($data[0]);
			
			if($this->rows($q)) {
				$row = $this->arr($q);
				return isset($data[1]) ? $row[$data[1]] : $row;
			}
			
			return json_encode([
				'status' => '2', 
				'message' => 'Отсутствуют записи.',
				'query' => $data[0]
			]);
		}
		
		public function __destruct() {
			$this->injector->close();
		}
	}