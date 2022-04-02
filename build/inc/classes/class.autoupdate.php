<?PHP
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	class AutoUpdate {
		private $token;
		protected $server = 'https://api.worksma.ru';
		
		public function __construct($token = null) {
			if(empty($token)) {
				return false;
			}
			
			$this->token = $token;
		}
		
		public function addLogs($msg) {
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/autoupdate.txt', date("[d.m.Y]") . " $msg\n", FILE_APPEND);
		}
		
		public function AddRemoteToken() {
			try {
				$result = json_decode($this->SendPost($this->server, [
					'type' => 'AddToken'
				]), true);
				
				var_dump($result);
				
				if(isset($result['alert'])) {
					switch($result['alert']) {
						case 'success':
							return true;
						break;
					}
				}
				
				result($result);
			}
			catch(Exception $e) {
				result([
					'alert' => 'error',
					'message' => $e->getMessage()
				]);
			}
		}
		
		public function SendPost($site, $postfiels) {
			$postfiels += [
				'domain' => $_SERVER['SERVER_NAME'],
				'version' => sys()->secondary()->version,
				'module' => 'autoupdate',
				'product' => 'uni-gamecms',
				'token' => $this->token
			];
			
			$ch = curl_init($site);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfiels));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HEADER, false);
			$result = curl_exec($ch);
			curl_close($ch);

			return $result;
		}
		
		public function Download($file, $remote) {
			$cInit = curl_init($remote);
			$fOpen = fopen($file, "wb");
			curl_setopt($cInit, CURLOPT_FILE, $fOpen);
			curl_setopt($cInit, CURLOPT_HEADER, 0);
			curl_exec($cInit);
			curl_close($cInit);
			
			return fclose($fOpen);
		}
	}