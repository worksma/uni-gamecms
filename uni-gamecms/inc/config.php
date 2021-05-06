<?PHP
	$safe_mode = 1; //безопасный режим - 1

	if(isset($conf->developer_mode)) {
		$dev_mode = $conf->developer_mode;
	} else {
		$dev_mode = 2;
	}

	if($dev_mode == 1 && $safe_mode != 1) {
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
	} else {
		ini_set('display_errors', 0);
		error_reporting(E_ALL);
	}
	if(isset($conf->time_zone)) {
		date_default_timezone_set($conf->time_zone);
	}

	if($protection == 1) {
		header('X-Frame-Options: DENY');
		header("X-XSS-Protection: 1; mode=block");
	}

	if(empty($conf->protocol) || $conf->protocol == 1) {
		$protocol = isset($_SERVER['HTTP_SCHEME']) ? $_SERVER['HTTP_SCHEME'] : ( ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 443 == $_SERVER['SERVER_PORT'] ) ? 'https' : 'http' );
	} elseif ($conf->protocol == 2) {
		$protocol = 'http';
	} elseif ($conf->protocol == 3) {
		$protocol = 'https';
	}

	$inactive_time = 900;
	$host = $_SERVER['SERVER_NAME'];
	$site_host = "../";
	$full_site_host = $protocol.'://'.$host.'/';

	spl_autoload_register(function ($name) {
		$folders = scandir($_SERVER['DOCUMENT_ROOT'].'/inc/classes/');
		for ($i=0; $i < count($folders); $i++) { 
			if(!is_file($folders[$i])) {
				$file = $_SERVER['DOCUMENT_ROOT'].'/inc/classes/'.$folders[$i].'/class.'.strtolower($name).'.php';
				if(file_exists($file)){
					require_once $file;
					return;
				}
			}
		}
		echo "Class ".$name." not found!";
	});