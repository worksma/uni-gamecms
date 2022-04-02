<?PHP
	$autoupdate = false;
	
	if(isset($_REQUEST) && $autoupdate) {
		$RAU = json_decode(file_get_contents("php://input"));
		
		$token = 'test';
		$backup = true;
		
		if(isset($RAU->autoupdate)) {
			if($RAU->token == $token) {
				$AU = new AutoUpdate($token);
				$AU->addLogs("Удачное подключение Токена");
				
				foreach($RAU->files as $file => $new) {
					$iFile = $_SERVER['DOCUMENT_ROOT'] . "/$file";
					
					if(file_exists($iFile)) {
						if(filesize($iFile) != $new[1]) {
							if(file_exists($iFile)) {
								if($backup) {
									$dirBackup = $_SERVER['DOCUMENT_ROOT'] . '/inc/backup/' . date("Y-m-d");
								
									if(!file_exists($dirBackup)) {
										mkdir($dirBackup);
									}
								
									$AU->addLogs("Создан бэкап: $file");
									file_put_contents("$dirBackup/$file", file_get_contents($iFile));
								}
							}
							
							$AU->addLogs("Обновлен файл: $file");
							$AU->Download($iFile, $new[0]);
						}
					}
					else {
						$AU->addLogs("Создан файл: $file");
						$AU->Download($iFile, $new[0]);
					}
				}
				
				die;
			}
			else {
				http_response_code('403');
				die;
			}
		}
	}