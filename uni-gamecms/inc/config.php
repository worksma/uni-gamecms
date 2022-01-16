<?PHP
	$safe_mode = 1; //безопасный режим - 1
	$dev_mode = isset($conf->developer_mode) ? $conf->developer_mode : 2;

	ini_set('display_errors', ($dev_mode == 1 && $safe_mode != 1) ? 1 : 0);
	error_reporting(E_ALL);
	date_default_timezone_set( isset($conf->time_zone) ? $conf->time_zone : 'Europe/Moscow');

	global $protection;

	if($protection == 1) {
		header('X-Frame-Options: DENY');
		header('X-XSS-Protection: 1; mode=block');
	}

	if(empty($conf->protocol) || $conf->protocol == 1) {
		$protocol = isset($_SERVER['HTTP_SCHEME'])
			? $_SERVER['HTTP_SCHEME']
			: (
			(
				(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
				|| 443 == $_SERVER['SERVER_PORT']
			)
				? 'https'
				: 'http'
			);
	} elseif($conf->protocol == 2) {
		$protocol = 'http';
	} elseif($conf->protocol == 3) {
		$protocol = 'https';
	}

	$inactive_time  = 900;
	$host           = $_SERVER['SERVER_NAME'];
	$site_host      = '../';
	$full_site_host = $protocol . '://' . $host . '/';

	require_once __DIR__ . '/autoloader.php';

	$SourceQuery = new SourceQuery;
	$Playground = new Playground($pdo, $conf);