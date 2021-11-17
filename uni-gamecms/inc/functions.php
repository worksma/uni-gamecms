<?php
function db_connect($db_host, $db_db, $db_user, $db_pass) {
	try {
		$pdo = new PDO(
			"mysql:host=" . $db_host . ";dbname=" . $db_db,
			$db_user,
			htmlspecialchars_decode($db_pass, ENT_QUOTES)
		);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		global $conf;

		if(isset($conf->secret)) {
			$file = get_log_file_name("pdo_errors");

			file_put_contents(
				$_SERVER["DOCUMENT_ROOT"] . "/logs/" . $file,
				"[" . date("Y-m-d H:i:s") . " | " . $db_host . " | " . $db_db . "] : [" . $e->getMessage() . "]\r\n",
				FILE_APPEND
			);
		}

		return false;
	}

	return $pdo;
}

function db_get_info($pdo = null, $what = null, $from = null, $where = null, $limit = null) {
	if(empty($pdo) || empty($what) || empty($from)) {
		return false;
	}

	$what = explode(' ', $what);
	$what_str = '';
	for($i = 0; $i < count($what); $i++) {
		$what_str .= '`'.clean($what[$i], null).'`,';
	}
	$what_str = substr($what_str, 0, -1);

	$from_str = clean($from, null);

	$where_str = '';
	if(preg_match("/([a-zA-Z0-9\"]{1,50}) ?(!=|=|<|>) ?([a-zA-Z0-9\"]{1,50})/", $where)) {
		$where_str = 'WHERE '.$where;
	}

	$limit_str = '';
	$limit = clean($limit, "int");
	if(!empty($limit)) {
		$limit_str = 'LIMIT '.$limit;
	}

	$STH = $pdo->query("SELECT $what_str FROM `$from_str` $where_str $limit_str");
	$STH->execute();
	$row = $STH->fetchAll();

	return $row;
}

function set_prefix($prefix, $table) {
	if(!empty($prefix)) {
		$table = $prefix.'_'.$table;
	}
	return $table;
}

function get_ai($pdo, $table, $column = 'id') {
	$STH = $pdo->query("SELECT $column FROM $table ORDER BY $column DESC LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	return $row->$column + 1;
}

function set_names($pdo, $code = 0) {
	switch($code):
		case "1":
			$pdo->exec("set names utf8");
		break;
		case "2":
			$pdo->exec("set names latin1");
		break;
		case "3":
			$pdo->exec("set names utf8mb4");
		break;
	endswitch;
	
	return true;
}

function check_table($table, $pdo) {
	$table = check($table, null);

	$STH = $pdo->prepare("SHOW TABLES LIKE :table");
	$STH->execute(array(":table" => "%".$table."%"));
	$row = $STH->fetchAll();
	$count = count($row);
	for($i = 0; $i < $count; $i++) {
		if(isset($row[0][$i]) && $table == $row[0][$i]) {
			return true;
		}
	}
	return false;
}

function check_column($table, $pdo, $column) {
	$table = check($table, null);

	$STH = $pdo->query("SHOW COLUMNS FROM $table");
	$STH->execute();
	$row = $STH->fetchAll();
	for($i = 0; $i < count($row); $i++) {
		if(isset($row[$i]['Field']) && ($row[$i]['Field'] == $column)) {
			return true;
		}
	}
	return false;
}

function get_rows_count($pdo, $table, $where) {
	$STH = $pdo->query("SELECT COUNT(*) as count FROM `$table` WHERE ".$where);
	$STH->setFetchMode(PDO::FETCH_ASSOC);
	$row = $STH->fetch();
	if(empty($row['count'])) {
		$row['count'] = 0;
	}
	return $row['count'];
}

function service_log($log, $admin, $server, $pdo, $service = 0) {
	$file = get_log_file_name("services_log");

	if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file)) {
		$i = "a";
	} else {
		$i = "w";
	}

	if(isset($_SESSION['id']) and isset($_SESSION['login'])) {
		$user = $_SESSION['login'] . ' - ' . $_SESSION['id'];
	} else {
		$user = 'Админ Центр';
	}

	$STH = $pdo->prepare("SELECT name FROM servers WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $server]);
	$server = $STH->fetch();

	$STH = $pdo->prepare("SELECT name FROM admins WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $admin]);
	$admin = $STH->fetch();

	if($service === 0) {
		$service = 'Все';
	} else {
		$STH = $pdo->prepare("SELECT name FROM services WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $service]);
		$service = $STH->fetch()->name;
	}

	$error_file = fopen($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file, $i);
	fwrite(
		$error_file,
		"[" . date("Y-m-d H:i:s")
		. " | Пользователь: " . $user
		. " | Сервер: " . $server->name
		. " | Идентификатор: " . $admin->name
		. " | Услуга: " . $service
		. "] : [" . $log . "] \r\n"
	);
	fclose($error_file);
}

function log_error($error) {
	$file = get_log_file_name("error_log");

	if(isset($_SESSION['login']) and isset($_SESSION['id'])) {
		$user = $_SESSION['login']." - ".$_SESSION['id'];
	} else {
		$user = "Гость";
	}

	if(file_exists($_SERVER['DOCUMENT_ROOT']."/logs/".$file)) {
		$i = "a";
	} else {
		$i = "w";
	}

	$error_file = fopen($_SERVER['DOCUMENT_ROOT']."/logs/".$file, $i);
	fwrite($error_file, "[".date("Y-m-d H:i:s")." | ".$_SERVER["REMOTE_ADDR"]." | ".$user."] : [".$error."] \r\n");
	fclose($error_file);
}

function write_log($log) {
	$file = get_log_file_name("log");

	if(isset($_SESSION['login']) and isset($_SESSION['id'])) {
		$user = $_SESSION['login']." - ".$_SESSION['id'];
	} else {
		$user = "Гость";
	}

	if(file_exists($_SERVER['DOCUMENT_ROOT']."/logs/".$file)) {
		$i = "a";
	} else {
		$i = "w";
	}

	$log_file = fopen($_SERVER['DOCUMENT_ROOT']."/logs/".$file, $i);
	fwrite($log_file, "[".date("Y-m-d H:i:s")." | ".$_SERVER["REMOTE_ADDR"]." | ".$user."] : [".$log."] \r\n");
	fclose($log_file);
}

function ValidateInt($variable) {
	if(is_numeric($variable) && $variable > 0 && (int)($variable) == $variable) {
		return true;
	} else {
		return false;
	}
}

function ValidateNameForUrl($variable) {
	if(preg_match("/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\#|\%|\^|\/|\\\|\&|\~|\*|\{|\}|\+|\:|\.|\,|\;|\`|\=|\(|\)|\§|\°]/", $variable)) {
		return true;
	} else {
		return false;
	}
}

function ValidateLetterAndNum($variable) {
	if(preg_match("/^[a-z\d]{1}[a-z\d\s]*[a-z\d]{1}$/i", $variable)) {
		return true;
	} else {
		return false;
	}
}

function clean($variable, $param = null) {
	$variable = magic_quotes($variable);
	$variable = htmlspecialchars($variable, ENT_QUOTES);
	$variable = trim($variable);

	//$variable = preg_replace('/[^(\x20-\x7F)]*/','', $variable); //crutch

	if($param == "int") {
		$variable = preg_replace('/[^0-9]+/', '', $variable);
		//$variable = abs(intval($variable));
	}
	if($param == "float") {
		$variable = str_replace(',', '.', $variable);
		$variable = preg_replace('/[^0-9.]/', '', $variable);
		$variable = (float)$variable;
		$variable = round($variable, 2);
	}
	return $variable;
}

function check($variable, $param) {
	if(isset($variable)) {
		$variable = clean($variable, $param);
		if($variable == '') {
			unset($variable);
		}
	}
	if(isset($variable)) {
		return $variable;
	} else {
		return null;
	}
}

function checkJs($variable, $param = null) {
	if(isset($variable)) {
		$variable = clean($variable, $param);
		if(isset($variable) and $variable == '') {
			unset($variable);
		}
		if(isset($variable) and $variable == 'undefined') {
			unset($variable);
		}
	}
	if(isset($variable)) {
		return $variable;
	} else {
		return null;
	}
}

function checkStart($variable) {
	if(isset($variable)) {
		$variable = clean($variable, "int");
		if($variable == '' and $variable != 0) {
			unset($variable);
		}
		if($variable == "undefined") {
			$variable = 0;
		}
		return $variable;
	} else {
		return null;
	}
}

function clean_name($name) {
	$name = trim(str_replace('#', "", $name));
	$name = trim(str_replace('/', "", $name));
	$name = trim(str_replace(':', "", $name));
	if(mb_strlen($name, 'UTF-8') > 230) {
		$name = substr($name, 230);
	}
	return $name;
}

function get_month($i, $type = 1) {
	if($type == 1) {
		$months = array(1  => 'января',
		                2  => 'февраля',
		                3  => 'марта',
		                4  => 'апреля',
		                5  => 'мая',
		                6  => 'июня',
		                7  => 'июля',
		                8  => 'августа',
		                9  => 'сентября',
		                10 => 'октября',
		                11 => 'ноября',
		                12 => 'декабря');
	} elseif($type == 2) {
		$months = array(1  => 'январь',
		                2  => 'февраль',
		                3  => 'март',
		                4  => 'апрель',
		                5  => 'май',
		                6  => 'июнь',
		                7  => 'июль',
		                8  => 'август',
		                9  => 'сентябрь',
		                10 => 'октябрь',
		                11 => 'ноябрь',
		                12 => 'декабрь');
	} else {
		$months = array(1 => '01', 2 => '02', 3 => '03', 4 => '04', 5 => '05', 6 => '06', 7 => '07', 8 => '08', 9 => '09', 10 => '10', 11 => '11', 12 => '12');
	}
	return $months[$i];
}

function expand_date($date, $type = 1) {
	if(clean($date, "int") == $date) {
		$time = $date;
	} else {
		$time = strtotime($date);
	}

	$month = get_month(date('n', $time), 1);
	$day = date('j', $time);
	$year = date('Y', $time);
	$hour = date('H', $time);
	$min = date('i', $time);

	if($type == 0) {
		return "$hour:$min";
	}
	if($type == 1) {
		return "$day $month $year г, $hour:$min";
	}
	if($type == 2) {
		return "$day $month $year г";
	}
	if($type == 3) {
		return "$day $month $year";
	}
	if($type == 4) {
		if($day < 10) {
			$day = "0".$day;
		}
		$month = get_month(date('n', $time), 3);
		$year = substr($year, 2);
		return "$day.$month.$year";
	}
	if($type == 5) {
		$dtnew['day'] = date('j', $time);
		$dtnew['year'] = date('Y', $time);
		$dtnew['hour'] = date('G', $time);
		$dtnew['min'] = date('i', $time);
		$dtnew['month'] = get_month(date('n', $time), 1);
		$dtnew['month2'] = get_month(date('n', $time), 2);
		$dtnew['month3'] = get_month(date('n', $time), 3);
		return $dtnew;
	}
	if($type == 6) {
		return "$day $month в $hour:$min";
	}

	$yesterday = strtotime('yesterday');

	if($type == 7) {
		$dif = time() - $time;
		if($dif < 59) {
			if($dif < 15) {
				return "Только что";
			} else {
				return $dif . " сек. назад";
			}
		} elseif($dif / 60 > 1 and $dif / 60 < 59) {
			return round($dif / 60) . " мин. назад";
		} elseif($dif / 3600 > 1 and $dif / 3600 < 23) {
			return round($dif / 3600) . " час. назад";
		} elseif($time > $yesterday && $time < ($yesterday + 24 * 3600)) {
			return "Вчера в $hour:$min";
		} elseif($time > ($yesterday - 24 * 3600) && $time < $yesterday) {
			return "Позавчера в $hour:$min";
		} else {
			return "$day $month $year г, $hour:$min";
		}
	}
	if($type == 8) {
		$dtnew['short'] = "$hour:$min";
		$dtnew['full'] = "$day $month $year г";
		return $dtnew;
	}

	return $date;
}

function expand_seconds($time) {
	if($time < 59) {
		$time = $time."сек.";
	} elseif($time / 60 > 1 and $time / 60 < 59) {
		$time = round($time / 60)." мин.";
	} elseif($time / 3600 > 1 and $time / 3600 < 23) {
		$time = round($time / 3600)." час.";
	} elseif($time / 86400 > 1 and $time / 86400 < 7) {
		$time = round($time / 86400)." сут.";
	} elseif($time / 86400 / 7 > 1 and $time < 60 * 60 * 24 * 365) {
		$time = round($time / 86400 / 7)." нед.";
	} elseif($time > 60 * 60 * 24 * 365) {
		$time = round($time / 60 / 60 / 24 / 365)." лет.";
	}

	return $time;
}

function expand_seconds2($seconds, $type = null) {
	if($seconds == 0) {
		return "Навсегда";
	}
	$days = (int)($seconds / (24 * 3600));
	$seconds -= $days * 24 * 3600;
	$hours = (int)($seconds / 3600);
	$seconds -= $hours * 3600;
	$minutes = (int)($seconds / 60);
	$seconds -= $minutes * 60;

	if($days != 0) {
		$days = $days." суток ";
	} else {
		$days = '';
	}
	if($hours != 0) {
		$hours = $hours." час. ";
	} else {
		$hours = '';
	}
	if($minutes != 0) {
		$minutes = $minutes." мин. ";
	} else {
		$minutes = '';
	}
	if($seconds != 0) {
		$seconds = $seconds." сек.";
	} else {
		$seconds = '';
	}
	if($type == 2) {
		return "{$days}{$hours}{$minutes}";
	} else {
		return "{$days}{$hours}{$minutes}{$seconds}";
	}
}

function diff_date($date1, $date2 = null) {
	$diff = array();

	if(!$date2) {
		$cd = getdate();
		$date2 = $cd['year'].'-'.$cd['mon'].'-'.$cd['mday'].' '.$cd['hours'].':'.$cd['minutes'].':'.$cd['seconds'];
	}

	$pattern = '/(\d+)-(\d+)-(\d+)(\s+(\d+):(\d+):(\d+))?/';
	preg_match($pattern, $date1, $matches);
	$d1 = array((int)$matches[1], (int)$matches[2], (int)$matches[3], (int)$matches[5], (int)$matches[6], (int)$matches[7]);
	preg_match($pattern, $date2, $matches);
	$d2 = array((int)$matches[1], (int)$matches[2], (int)$matches[3], (int)$matches[5], (int)$matches[6], (int)$matches[7]);

	for($i = 0; $i < count($d2); $i++) {
		if($d2[$i] > $d1[$i])
			break;
		if($d2[$i] < $d1[$i]) {
			$t = $d1;
			$d1 = $d2;
			$d2 = $t;
			break;
		}
	}

	$md1 = array(31, $d1[0] % 4 || (!($d1[0] % 100) && $d1[0] % 400) ? 28 : 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	$md2 = array(31, $d2[0] % 4 || (!($d2[0] % 100) && $d2[0] % 400) ? 28 : 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	$min_v = array(null, 1, 1, 0, 0, 0);
	$max_v = array(null, 12, $d2[1] == 1 ? $md2[11] : $md2[$d2[1] - 2], 23, 59, 59);
	for($i = 5; $i >= 0; $i--) {
		if($d2[$i] < $min_v[$i]) {
			$d2[$i - 1]--;
			$d2[$i] = $max_v[$i];
		}
		$diff[$i] = $d2[$i] - $d1[$i];
		if($diff[$i] < 0) {
			$d2[$i - 1]--;
			$i == 2 ? $diff[$i] += $md1[$d1[1] - 1] : $diff[$i] += $max_v[$i] - $min_v[$i] + 1;
		}
	}

	return $diff;
}

function getPhrase($number, $titles) {
	$cases = array(2, 0, 1, 1, 1, 2);

	return $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
}

function translit($url) {
	$transsimvol = array("А" => "a",
	                     "Б" => "b",
	                     "В" => "v",
	                     "Г" => "g",
	                     "Д" => "d",
	                     "Е" => "e",
	                     "Ж" => "zh",
	                     "З" => "z",
	                     "И" => "i",
	                     "Й" => "y",
	                     "К" => "k",
	                     "Л" => "l",
	                     "М" => "m",
	                     "Н" => "n",
	                     "О" => "o",
	                     "П" => "p",
	                     "Р" => "r",
	                     "С" => "s",
	                     "Т" => "t",
	                     "У" => "u",
	                     "Ф" => "f",
	                     "Х" => "h",
	                     "Ц" => "ts",
	                     "Ч" => "ch",
	                     "Ш" => "sh",
	                     "Щ" => "sch",
	                     "Ъ" => "",
	                     "Ы" => "y",
	                     "Ь" => "",
	                     "Э" => "e",
	                     "Ю" => "yu",
	                     "Я" => "ya",
	                     " " => "_",
	                     "а" => "a",
	                     "б" => "b",
	                     "в" => "v",
	                     "г" => "g",
	                     "д" => "d",
	                     "е" => "e",
	                     "ж" => "j",
	                     "з" => "z",
	                     "и" => "i",
	                     "й" => "y",
	                     "к" => "k",
	                     "л" => "l",
	                     "м" => "m",
	                     "н" => "n",
	                     "о" => "o",
	                     "п" => "p",
	                     "р" => "r",
	                     "с" => "s",
	                     "т" => "t",
	                     "у" => "u",
	                     "ф" => "f",
	                     "х" => "h",
	                     "ц" => "ts",
	                     "ч" => "ch",
	                     "ш" => "sh",
	                     "щ" => "sch",
	                     "ъ" => "",
	                     "ы" => "y",
	                     "ь" => "",
	                     "э" => "e",
	                     "ю" => "yu",
	                     "я" => "ya");
	return strtr($url, $transsimvol);
}

function get_file_name() {
	$filename = substr(basename($_SERVER['PHP_SELF']), 0, strrpos(basename($_SERVER['PHP_SELF']), '.'));
	return $filename;
}

function get_ip() {
	$serverVars = array("HTTP_X_FORWARDED_FOR",
	                    "HTTP_X_FORWARDED",
	                    "HTTP_FORWARDED_FOR",
	                    "HTTP_FORWARDED",
	                    "HTTP_VIA",
	                    "HTTP_X_COMING_FROM",
	                    "HTTP_COMING_FROM",
	                    "HTTP_CLIENT_IP",
	                    "HTTP_XROXY_CONNECTION",
	                    "HTTP_PROXY_CONNECTION",
	                    "HTTP_USERAGENT_VIA");
	foreach($serverVars as $serverVar) {
		if(!empty($_SERVER) && !empty($_SERVER[$serverVar])) {
			$proxyIP = $_SERVER[$serverVar];
		} elseif(!empty($_ENV) && isset($_ENV[$serverVar])) {
			$proxyIP = $_ENV[$serverVar];
		} elseif(@getenv($serverVar)) {
			$proxyIP = getenv($serverVar);
		}
	}
	if(!empty($proxyIP)) {
		$isIP = preg_match('|^([0-9]{1,3}\.){3,3}[0-9]{1,3}|', $proxyIP, $regs);
		if(isset($regs[0])) {
			$long = ip2long($regs[0]);
			if($isIP && (sizeof($regs) > 0) && $long != -1 && $long !== false) {
				if(filter_var($regs[0], FILTER_VALIDATE_IP)) {
					return clean($regs[0], null);
				}
			}
		}
	}
	if(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
		return clean($_SERVER['REMOTE_ADDR'], null);
	} else {
		return '127.0.0.1';
	}
}

function get_groups($pdo) {
	$STH = $pdo->query("SELECT * FROM users__groups");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$users_groups[$row->id]['name'] = $row->name;
		$users_groups[$row->id]['color'] = $row->color;
		$users_groups[$row->id]['rights'] = $row->rights;
		$users_groups[$row->id]['id'] = $row->id;
	}
	$users_groups[0]['name'] = "Гость";
	$users_groups[0]['color'] = "#CCCCCC";
	$users_groups[0]['rights'] = "0";
	$users_groups[0]['id'] = "0";
	return $users_groups;
}

function users_groups() {
	global $users_groups;
	
	if(empty($users_groups)) {
		$users_groups = get_groups(pdo());
	}

	return $users_groups;
}

function user() {
	if(!is_auth()) {
		$user = null;
	}
	else {
		global $user;
	}
	
	return $user;
}


function dell_old_users($pdo, $site_name) {
	$toRemove = [];

	$STH = $pdo->query("SELECT id, regdate FROM users WHERE active = '0'");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		if(time() - strtotime($row->regdate) > 24 * 60 * 60) {
			$toRemove[] = $row->id;
		}
	}

	foreach($toRemove as $userToRemove) {
		$STH = $pdo->prepare("SELECT id, login, email FROM users WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $userToRemove]);
		$row = $STH->fetch();

		$pdo->exec("DELETE FROM users WHERE id='$userToRemove' LIMIT 1");
		$pdo->exec("DELETE FROM events WHERE data_id='$userToRemove' AND type='3' LIMIT 1");

		write_log("Удален пользователь c ID:".$row->id." Login:".$row->login." из-за неактивации аккаунта");
		incNotifications();
		$letter = dell_user_letter($site_name, $row->login);
		sendmail($row->email, $letter['subject'], $letter['message'], $pdo);
	}
}

function createDir($path, $type, $type2 = null) {
	if(empty($path)) {
		$path = '.';
	}
	if($handle = opendir($path)) {

		if($type2 == null) {
			$data = '<ol class="tree">';
		}

		while(false !== ($file = readdir($handle))) {
			if(is_dir($path.$file) && $file != '.' && $file != '..')
				$data .= printSubDir($type, $file, $path); else if($file != '.' && $file != '..')
				$queue[] = $file;
		}

		if(empty($data)) {
			$data = '';
		}

		if(!empty($queue)) {
			$data .= printQueue($queue, $path, $type);
		}


		if($type2 == null) {
			$data .= "</ol>";
		}
	}
	return $data;
}

function printQueue($queue, $path, $type) {
	asort($queue);
	foreach($queue as $file) {
		if(empty($data)) {
			$data = '';
		}
		$data .= printFile($file, $path, $type);
	}
	return $data;
}

function printFile($file, $path, $type) {
	if(empty($data)) {
		$data = '';
	}
	$data .= "<li class=\"file\"><a onclick=\"get_content_tpl('".$path.$file."', '".$type."');\" alt=\"".$path.$file."\" class=\"c-p\">$file</a></li>";
	return $data;
}

function printSubDir($type, $dir, $path) {
	if(empty($data)) {
		$data = '';
	}
	$data .= "<li class=\"toggle\">$dir<input type=\"checkbox\">";
	$data .= createDir($path.$dir."/", $type);
	$data .= "</li>";
	return $data;
}

function collect_tpl($info, $tpl) {
	$data = $tpl;
	for($i = 0; $i < count($info); $i++) {
		$data = str_replace('{'.$info[$i]['name'].'}', $info[$i]['var'], $data);
	}
	return $data;
}

function write_sitemap($url) {
	$file = $_SERVER['DOCUMENT_ROOT']."/sitemap.xml";
	if(file_exists($file) and filesize($file) != 0) {
		$data = '';
		$i = "a";
		dell_last_string($file);
	} else {
		$data = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$i = "w";
	}

	$date = date("Y-m-d");
	$data .= "	<url>
		<loc>".$url."</loc>
		<lastmod>".$date."</lastmod>
		<changefreq>always</changefreq>
	</url>
</urlset>";
	$log_file = fopen($file, $i);
	fwrite($log_file, $data);
	fclose($log_file);
}

function dell_last_string($history_file) {
	$filesize = filesize($history_file);
	if($filesize < 1) {
		return false;
	}

	if($filesize < 1024) {
		$filesize = 1024;
	}

	$fp2 = fopen($history_file, 'r');
	fseek($fp2, $filesize - 1024);
	$last_data = 0;

	while($buffer = fgets($fp2, 1024)) {
		$last_data = $buffer;
	}

	fclose($fp2);
	$fz = filesize($history_file);
	$st = strlen($last_data);

	if($fz == $st) {
		$fp2 = fopen($history_file, 'w');
		fclose($fp2);
		return true;
	}

	$fp2 = fopen($history_file, 'r+');
	ftruncate($fp2, $fz - $st - 1);
	fclose($fp2);

	return true;
}

function getMonitoringUrl() {
	global $config_additional;

	if(
		!empty($config_additional)
		&& array_key_exists('monitoring_url', $config_additional)
	) {
		$monitoringUrl = $config_additional['monitoring_url'];
	} else {
		$monitoringUrl = 'https://analytics.worksma.ru/';
	}

	return $monitoringUrl;
}

function update_monitoring($pdo = null) {
	if(empty($pdo)):
		return null;
	endif;

	$conf2 = pdo()->query("SELECT `mon_gap`, `mon_time`, `mon_api`, `mon_key` FROM `config__secondary` LIMIT 1")->fetch(PDO::FETCH_OBJ);
	
	if((time() - $conf2->mon_time) > $conf2->mon_gap):
		switch($conf2->mon_api):
			case 1:
				$servers = pdo()->query("SELECT `ip`, `port` FROM `servers` WHERE `show`='1' ORDER BY `trim`")->fetchAll();

				if(isset($servers)):
					$servers = curl_get_process([
						'website' => getMonitoringUrl() . 'handler.php',
						'data' => '&key=' . $conf2->mon_key . '&servers=' . serialize($servers)
					]);
				else:
					$servers = null;
				endif;

				if($servers != '403'):
					$servers = unserialize($servers);
				endif;

				pdo()->exec("DELETE FROM `monitoring`");

				$servers_sec = pdo()->query("SELECT `address`, `id`, `ip`, `port`, `type`, `game` FROM `servers` WHERE `show`='1' ORDER BY `trim`")->fetchAll();

				$count = count($servers_sec);

				$temp = pdo()->query("SELECT `id` FROM `monitoring` LIMIT 1")->fetch(PDO::FETCH_OBJ);

				if(isset($temp->id)):
					pdo()->exec("DELETE FROM `monitoring`");
				endif;

				for($i = 0; $i < $count; $i++):
					if(empty($servers[$i]['info']['HostName'])):
						$servers_name = 0;
					else:
						$servers_name = $servers[$i]['info']['HostName'];
					endif;

					if(empty($servers[$i]['info']['Map'])):
						$servers_map = 0;
					else:
						$servers_map = $servers[$i]['info']['Map'];

						if(strpos($servers_map, '/') !== false):
							$servers_map = explode("/", $servers_map);
							$servers_map = end($servers_map);
						endif;
					endif;

					if(empty($servers[$i]['info']['MaxPlayers'])):
						$players_max = 0;
					else:
						$players_max = $servers[$i]['info']['MaxPlayers'];
					endif;

					if(empty($servers[$i]['info']['Players'])):
						$players_now = 0;
					else:
						$players_now = $servers[$i]['info']['Players'];
					endif;

					pdo()->prepare("INSERT INTO `monitoring` (`address`, `sid`, `ip`, `port`, `name`, `game`, `players_now`, `players_max`, `map`, `type`) VALUES (:address, :sid, :ip, :port, :name, :game, :players_now, :players_max, :map, :type)")->execute([
						'address' => $servers_sec[$i]['address'],
						'sid' => $servers_sec[$i]['id'],
						'ip' => $servers_sec[$i]['ip'],
						'port' => $servers_sec[$i]['port'],
						'name' => $servers_name,
						'game' => $servers_sec[$i]['game'],
						'players_now' => $players_now,
						'players_max' => $players_max,
						'map' => $servers_map,
						'type' => $servers_sec[$i]['type']
					]);
				endfor;
			break;

			default:
				pdo()->exec("DELETE FROM `monitoring`");
				$temp = pdo()->query("SELECT `id` FROM `monitoring` LIMIT 1")->fetch(PDO::FETCH_OBJ);

				if(isset($temp->id)):
					pdo()->exec("DELETE FROM `monitoring`");
				endif;

				$sth = pdo()->query("SELECT * FROM `servers` WHERE `show`='1' ORDER BY `trim`");

				while($row = $sth->fetch(PDO::FETCH_OBJ)):
					SourceQuery()->Connect($row->ip, $row->port, 1);
					$GetInfo = SourceQuery()->GetInfo();
					SourceQuery()->Disconnect();

					pdo()->prepare("INSERT INTO `monitoring` (`address`, `sid`, `ip`, `port`, `name`, `game`, `players_now`, `players_max`, `map`, `type`) VALUES (:address, :sid, :ip, :port, :name, :game, :players_now, :players_max, :map, :type)")->execute([
						'address' => $row->address,
						'sid' => $row->id,
						'ip' => $row->ip,
						'port' => $row->port,
						'name' => isset($GetInfo['HostName']) ? clean($GetInfo['HostName'], null) : 0,
						'game' => $row->game,
						'players_now' => isset($GetInfo['Players']) ? $GetInfo['Players'] : 0,
						'players_max' => isset($GetInfo['MaxPlayers']) ? $GetInfo['MaxPlayers'] : 0,
						'map' => isset($GetInfo['Map']) ? $GetInfo['Map'] : 0,
						'type' => $row->type
					]);
				endwhile;
			break;
		endswitch;

		pdo()->prepare("UPDATE `config__secondary` SET `mon_time` =:mon_time WHERE `id`='1' LIMIT 1")->execute([
			'mon_time' => time()
		]);
	endif;
}

function removeDirectory($dir, $remove_dir = 1) {
	if($objs = glob($dir."/*")) {
		foreach($objs as $obj) {
			is_dir($obj) ? removeDirectory($obj) : unlink($obj);
		}
	}
	if($remove_dir == 1) {
		if($objs = glob($dir."/.htaccess")) {
			foreach($objs as $obj) {
				is_dir($obj) ? removeDirectory($obj) : unlink($obj);
			}
		}
	}
	if($objs = glob($dir."/.eslintrc")) {
		foreach($objs as $obj) {
			is_dir($obj) ? removeDirectory($obj) : unlink($obj);
		}
	}
	if($objs = glob($dir."/*")) {
		foreach($objs as $obj) {
			is_dir($obj) ? removeDirectory($obj) : unlink($obj);
		}
	}
	if($remove_dir == 1) {
		rmdir($dir);
	}
	return true;
}

function strip_data($text) {
	$quotes = array("\x27", "\x22", "\x60", "\t", "\n", "\r", "%");
	$goodquotes = array("-", "+", "#");
	$repquotes = array("\-", "\+", "\#");
	$text = trim(strip_tags($text));
	$text = str_replace($quotes, '', $text);
	$text = str_replace($goodquotes, $repquotes, $text);
	$text = preg_replace("/ +/", " ", $text);

	return $text;
}

function sendmail($mail_to, $subject, $message, $pdo, $type = 0, $debug = 0) {
	if($type == 1 and $mail_to == 'none') {
		$STH = $pdo->query("SELECT `admins_ids` FROM `config__secondary` LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		$user_ids = explode(",", $row->admins_ids);
		$ids_count = count($user_ids);
		for($i = 0; $i < $ids_count; $i++) {
			$STH = $pdo->prepare("SELECT `email`, `email_notice` FROM `users` WHERE `id`=:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':id' => $user_ids[$i]));
			$row = $STH->fetch();
			if($row->email_notice == 1) {
				sendmail($row->email, $subject, $message, $pdo);
			}
		}
	} else {
		if(substr($mail_to, 0, 6) != 'vk_id_' && !empty($mail_to)) {
			$STH = $pdo->query("SELECT * FROM config__email LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$email_conf = $STH->fetch();
			$message = str_replace("\n", '<br>', $message);
			if($email_conf->use_email == 2) {
				mail($mail_to, $subject, $message, "Content-type: text/html; charset=utf8 \r\n");
			} else {
				$mail = new phpmailer(true);
				if($email_conf->verify_peers == 2) {
					$mail->SMTPOptions = array('ssl' => array('verify_peer'       => false,
					                                          'verify_peer_name'  => false,
					                                          'allow_self_signed' => true));
				}
				$mail->IsSMTP();
				$mail->Host = $email_conf->host;
				$mail->SMTPAuth = true;
				$mail->SMTPDebug = $debug;
				$mail->CharSet = $email_conf->charset;
				$mail->Port = $email_conf->port;
				$mail->Username = $email_conf->username;
				$mail->Password = $email_conf->password;
				$mail->AddReplyTo($email_conf->username, $email_conf->from_email);
				/** @noinspection PhpUnhandledExceptionInspection */
				$mail->SetFrom($email_conf->username, $email_conf->from_email);
				$mail->AddAddress($mail_to);
				$mail->Subject = htmlspecialchars($subject);
				$mail->MsgHTML($message);
				/** @noinspection PhpUnhandledExceptionInspection */
				$mail->Send();
			}
		}
	}
}

function up_online($pdo) {
	if(isset($_COOKIE['id'])) {
		$_SESSION['id'] = clean($_COOKIE['id'], "int");
	}

	$time = time();
	if(isset($_SESSION['id'])) {
		$STH = $pdo->query("SELECT `id` FROM `users__online` WHERE `user_id`='$_SESSION[id]' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$tmp = $STH->fetch();
		if(empty($tmp->id)) {
			$STH = $pdo->prepare("INSERT INTO `users__online` (`user_id`,`time`) values (:user_id, :time)");
			$STH->execute(array('user_id' => $_SESSION['id'], 'time' => $time));
		} else {
			$STH = $pdo->prepare("UPDATE `users__online` SET `time`=:time WHERE `user_id`='$_SESSION[id]' LIMIT 1");
			$STH->execute(array(':time' => $time));
		}
	}
}

function show_error_page($error_type = '404') {
	global $messages;
	if($error_type == '404') {
		$_SESSION['error_msg'] = $messages['404'];
		foreach($GLOBALS as $key => $val) {
			global $$key;
		}
		/** @noinspection PhpUnusedLocalVariableInspection */
		$page = $PI->page_info('error_page');
		$tpl->dir = $_SERVER["DOCUMENT_ROOT"].'/templates/'.$conf->template.'/tpl/';
		include_once $_SERVER["DOCUMENT_ROOT"]."/modules/error/index.php";
		exit();
	} elseif($error_type == 'not_auth') {
		$_SESSION['error_msg'] = $messages['Not_auth'];
	} elseif($error_type == 'not_adm') {
		$_SESSION['error_msg'] = $messages['Not_adm'];
	} elseif($error_type == 'not_allowed') {
		$_SESSION['error_msg'] = $messages['Not_allowed'];
	} elseif($error_type == 'not_settings') {
		$_SESSION['error_msg'] = $messages['Not_settings'];
	} elseif($error_type == 'wrong_url') {
		$_SESSION['error_msg'] = $messages['Wrong_url'];
	}
	
	http_response_code(403);
	header('Location: ../error_page');
	exit();
}

function check_img($matches) {
	if(@exif_imagetype(trim($matches[0])) == false) {
		return $matches[0];
	} else {
		return ('<br><a href="'.$matches[0].'" class="thumbnail" data-lightbox="'.mt_rand(0, 100).'"><img src="'.$matches[0].'" class="thumbnail-img" alt=""></a><br>');
	}
}

function find_img_mp3($text, $id, $not_img = 0) {
	$ok = 0;
	$length = mb_strlen($text, 'UTF-8');
	if($length > 17) {
		$col = substr_count($text, ' ');
		if($col == 0) {
			$http = substr($text, 0, 7);
			//$ras = substr($text, $length - 4, $length);
			if($http == 'sticker') {
				if(substr(substr($text, 7), 0, 18) != '../files/stickers/') {
					$text = check($text, null);
				} else {
					$text = '<img class="g_sticker" src="'.substr($text, 7).'">';
				}
				$ok = 1;
			}
		}
		if($ok != 1) {
			if(preg_match('#(http://[^\s]+(?=\.(mp3|mp4)))#i', $text)) {
				//$val = mt_rand(0, 100);
				$text = preg_replace('#(http://[^\s]+(?=\.(mp3|mp4)))(\.(mp3|mp4))#i', '<audio src="$1.$2" controls="controls">Аудио файл: $1.$2</audio>', $text);
			}
			if($not_img == 0) {
				if(preg_match('#((http|https)://[^\s]+(?=\.(jpe?g|png|gif|bmp)))#i', $text)) {
					$text = preg_replace_callback('#((http|https)://[^\s]+(?=\.(jpe?g|png|gif|bmp)))(\.(jpe?g|png|gif))#i', "check_img", $text);
				}
				$text = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<span class=\"m-icon icon-link\"></span><a href=\"$3\" target=\"_blank\" title=\"Мы не несем ответственности за ресурс, на который направлена ссылка\">$3</a>", $text);
				$text = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<span class=\"m-icon icon-link\"></span><a href=\"http://$3\" target=\"_blank\" title=\"Мы не несем ответственности за ресурс, на который направлена ссылка\">$3</a>", $text);
				if(preg_match("/(http|https):\/\/(www.youtube|youtube|youtu)\.(be|com)\/([^<\s]*)/", $text, $match)) {
					if(preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $text, $id)) {
						$values = $id[1];
					} else if(preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $text, $id)) {
						$values = $id[1];
					} else if(preg_match('/youtube\.com\/v\/([^\&\?\/]+)/', $text, $id)) {
						$values = $id[1];
					} else if(preg_match('/youtu\.be\/([^\&\?\/]+)/', $text, $id)) {
						$values = $id[1];
					} else if(preg_match('/youtube\.com\/verify_age\?next_url=\/watch%3Fv%3D([^\&\?\/]+)/', $text, $id)) {
						$values = $id[1];
					}
					$text = '<iframe width="400" height="200" src="https://www.youtube.com/embed/'.$values.'" frameborder="0" allowfullscreen></iframe>';
				}
			}
		}
	}
	if($ok != 1) {
		$smiles_key = array();
		for($i = 0; $i < 63; $i++) {
			$j = $i + 1;
			if($j < 10) {
				$j = "0".$j;
			}
			$smiles_key[$i] = ":smile".$j.":";
		}
		for($i = 1; $i <= count($smiles_key); $i++) {
			$smiles_value[$i] = "<img class='g_smile' src='../files/smiles/".$i.".png'>";
		}
		$text = str_replace($smiles_key, $smiles_value, $text);
	}
	return $text;
}

function clean_str($str) {
	return preg_replace('/[^a-zA-Zа-яёЁА-Я0-9._ ]/ui', '', $str);
}

function clean_from_php($data) {
	global $safe_mode;
	if($safe_mode == 1) {
		$data = preg_replace('/<(\?php|\?)(.*?)\?>/is', '', $data);
	}

	return $data;
}

function check_for_php($data) {
	global $safe_mode;
	if($safe_mode == 1) {
		if(preg_match('/<(\?php|\?).*?\?>/is', $data) || stristr($data, '<?php') !== false || stristr($data, '<?') !== false) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function magic_quotes($data) {
	$phpVersion = getPhpVersion();
	$phpVersion = $phpVersion[0] + $phpVersion[1] * 0.1;

	if($phpVersion > 5 && $phpVersion < 7.4) {
		if(
			function_exists('get_magic_quotes_gpc')
			&& get_magic_quotes_gpc()
		) {
			$data = stripslashes($data);
		}
	}

	return $data;
}

function crate_pass($max, $type) {
	if($type == 1) {
		$chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
	} elseif($type == 2) {
		$chars = "1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
	}
	$size = StrLen($chars) - 1;
	$password = null;
	while($max--)
		$password .= $chars[rand(0, $size)];

	return $password;
}

function calculate_size($size) {
	if($size > 1024) {
		$size = round($size / 1024, 2);
		$ed = "Кбайт";
		if($size > 1024) {
			$size = round($size / 1024, 2);
			$ed = "Мбайт";
		}
	} else {
		$ed = "байт";
	}
	return $size.' '.$ed;
}

function code_str($str, $password = "") {
	$salt = "2f53g648";
	$len = strlen($str);
	$gamma = '';
	$n = $len > 100 ? 8 : 2;
	while(strlen($gamma) < $len) {
		$gamma .= substr(pack('H*', sha1($password.$gamma.$salt)), 0, $n);
	}
	return $str ^ $gamma;
}

function exec_script($url, $params = array()) {
	$parts = parse_url($url);

	if(!$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80)) {
		return false;
	}

	$data = http_build_query($params, '', '&');

	fwrite($fp, "POST ".(!empty($parts['path']) ? $parts['path'] : '/')." HTTP/1.1\r\n");
	fwrite($fp, "Host: ".$parts['host']."\r\n");
	fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
	fwrite($fp, "Content-Length: ".strlen($data)."\r\n");
	fwrite($fp, "Connection: Close\r\n\r\n");
	fwrite($fp, $data);
	while(!feof($fp))
		$answer = fgets($fp, 1000);
	fclose($fp);

	return $answer;
}

function get_ban_admin_nick($admin_nick, $admin_nick2, $server_name, $type) {
	if(!empty($admin_nick2)) {
		$admin_nick = $admin_nick2;
	} elseif(empty($admin_nick)) {
		$admin_nick = $server_name;
	}
	if($type == '2' || $type == '3' || $type == '5') {
		$admin_nick = htmlspecialchars_decode($admin_nick);
	}
	return $admin_nick;
}

function copy_files($source, $res) {
	$hendle = opendir($source);
	while($file = readdir($hendle)) {
		if(($file != ".") && ($file != "..")) {
			if(is_dir($source."/".$file) == true) {
				if(is_dir($res."/".$file) != true)
					mkdir($res."/".$file, 0777);
				copy_files($source."/".$file, $res."/".$file);
			} else {
				if(!copy($source."/".$file, $res."/".$file)) {
					log_error("An error occurred while copying the file: $file\n");
				}
			}
		}
	}
	closedir($hendle);
}

function collect_reit($thanks, $answers) {
	//thanks - количество спасибок
	//answers - количество ответов
	$reit = $thanks * 5 + $answers;
	return $reit;
}

function check_remote_file($file, $timeOut = null) {

	if(!is_null($timeOut)) {
		$default = stream_context_get_options(stream_context_get_default());
		stream_context_set_default(
			[
				'http' => [
					'timeout' => $timeOut,
				]
			]
		);
	}

	$headers = @get_headers($file, true);

	if(
		!empty($headers)
		&& is_array($headers)
		&& strpos($headers[0], '200')
	) {
		$result = true;
	} else {
		$result = false;
	}

	if(isset($default)) {
		stream_context_set_default($default);
	}

	return $result;
}

function get_log_file_name($name) {
	global $conf;
	$file_prefix = md5($conf->secret);

	return $name."_".$file_prefix.".log";
}

function get_log_file($file) {
	if(file_exists($file)) {
		if(filesize($file) > 1 * 1024 * 1024) {
			$rename = substr($file, 0, -4)."_".date("Y-m-d_H-i-s").".txt";
			rename($file, $rename);

			$log_file = fopen($file, "w");
			fwrite($log_file, "[".date("Y-m-d H:i:s")." ] : [Создан новый лог, прошлый переименован в ".$rename."] \r\n");
			fclose($log_file);
		}
		$log = file_get_contents($file);
		$log = nl2br($log);
		$log = explode('<br />', $log);
		$log = array_reverse($log);
		array_shift($log);
		$log = implode("<br>", $log);
	} else {
		$log = '';
	}

	return $log;
}

function get_active($val, $type, $type2 = 1) {
	if($type2 == 1) {
		$name = 'active';
	} else {
		$name = 'selected';
	}
	if($type == 1) {
		if($val == 1) {
			$array = array($name, '');
		} elseif($val == 0) {
			$array = array('', $name);
		}
	} elseif($type == 2) {
		if($val == 1) {
			$array = array($name, '');
		} elseif($val == 2) {
			$array = array('', $name);
		}
	} elseif($type == 3) {
		if($val == 0) {
			$array = array('', $name, 0);
		} else {
			$array = array($name, '', $val);
		}
	} elseif($type == 4) {
		if($val == 2) {
			$array = array('', $name, '');
		} else {
			$array = array($name, '', $val);
		}
	}
	return $array;
}

function file_get_contents_curl($url) {
	$url = str_replace("&amp;", "&", $url);
	return @file_get_contents($url);
}

function get_procent($val1, $val2) {
	if($val1 > 0) {
		return round(number_format($val2 / $val1, 2));
	} else {
		return 0;
	}
}

function str_replace_once($search, $replace, $text) {
	$pos = strpos($text, $search);
	return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
}

function calculate_discount($server, $global, $user, $service = 0, $tarif = 0) {
	if(empty($server)) {
		$server = 0;
	}
	if(empty($global)) {
		$global = 0;
	}
	if(empty($user)) {
		$user = 0;
	}

	if($tarif != 0) {
		if($tarif > $user) {
			return $tarif;
		} else {
			return $user;
		}
	} elseif($service != 0) {
		if($service > $user) {
			return $service;
		} else {
			return $user;
		}
	} elseif($server != 0) {
		if($server > $user) {
			return $server;
		} else {
			return $user;
		}
	} elseif($global != 0) {
		if($global > $user) {
			return $global;
		} else {
			return $user;
		}
	} elseif($user != 0) {
		return $user;
	} else {
		return 0;
	}
}

function calculate_price($price, $discount) {
	$temp = $price - $price * $discount / 100;
	if($temp == $price) {
		return $price;
	} else {
		return round($temp, 2);
	}
}

function calculate_return($price, $time) {
	if($time != 0) {
		$temp = $price / $time;
		return round($temp, 2);
	} else {
		return 0;
	}
}

function round_shilings($shilings = 0) {
	if(empty($shilings)) {
		return 0;
	} else {
		return round($shilings, 2);
	}
}

function collect_consumption_str($kind, $type, $class, $name, $pdo = null, $user_id = 0) {
	if($user_id != 0) {
		$STH = $pdo->prepare("SELECT `login` FROM `users` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $user_id));
		$row = $STH->fetch();
		if(!empty($row->login)) {
			$login = $row->login;
		}
	}
	if(empty($row->login) || $user_id == 0) {
		$login = 'unnamed';
	}

	$result = '';
	if($kind == 1) {
		$result = '<p class="text-'.$class.'">'.$name.'</p>';
	} elseif($kind == 2) {
		if($type == 1) {
			$result = '<b><p class="text-success">'.$name.'</p></b>';
		} elseif($type == 3) {
			$result = '<p class="text-danger">'.$name.'</p>';
		} else {
			$result = '<p>'.$name.'</p>';
		}
	}
	if($type == 3 || $type == 11) {
		$result = str_replace("{id}", $user_id, $result);
		$result = str_replace("{login}", $login, $result);
	}

	return $result;
}

function clip_image($im, $size, $file_path) {
	if(empty($size)) {
		$size = 300;
	}
	$w = $size;
	$w_src = imagesx($im);
	$h_src = imagesy($im);
	$dest = imagecreatetruecolor($w, $w);
	if($w_src > $h_src) {
		imagecopyresampled($dest, $im, 0, 0, round((max($w_src, $h_src) - min($w_src, $h_src)) / 2), 0, $w, $w, min($w_src, $h_src), min($w_src, $h_src));
	}
	if($w_src < $h_src) {
		imagecopyresampled($dest, $im, 0, 0, 0, 0, $w, $w, min($w_src, $h_src), min($w_src, $h_src));
	}
	if($w_src == $h_src) {
		imagecopyresampled($dest, $im, 0, 0, 0, 0, $w, $w, $w_src, $w_src);
	}
	imagejpeg($dest, $_SERVER["DOCUMENT_ROOT"].'/'.$file_path.'.jpg');

	return true;
}

function if_img($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'jpg') == 0) || (strcasecmp($extension, 'jpeg') == 0) || (strcasecmp($extension, 'png') == 0) || (strcasecmp($extension, 'gif') == 0) || (strcasecmp($extension, 'bmp') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_jpg($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'jpg') == 0) || (strcasecmp($extension, 'jpeg') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_png($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'png') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_gif($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'gif') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_zip($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'zip') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_mp3($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'mp3') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_archive($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'rar') == 0) || (strcasecmp($extension, 'zip') == 0) || (strcasecmp($extension, '7z') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_ico($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'ico') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_scss($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'scss') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_date($day, $month, $year) {
	return checkdate($month, $day, $year);
}

function set_temp_file_name($name) {
	$extension = explode(".", $name);
	$extension = end($extension);

	return md5($name).".".$extension;
}

function get_editor_settings($pdo) {
	$STH = $pdo->prepare("SELECT `data` FROM `config__strings` WHERE `id`=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => 2));
	$row = $STH->fetch();
	$settings = unserialize($row->data);

	if($settings['file_manager'] == 1) {
		$settings['file_manager'] = 'responsivefilemanager';
	} else {
		$settings['file_manager'] = '';
	}
	if($settings['file_manager_theme'] == 1) {
		$settings['file_manager_theme'] = 'oxide';
	} else {
		$settings['file_manager_theme'] = 'oxide-dark';
	}

	return $settings;
}

function preg_icon($data) {
	$data = preg_replace('/{ ?glyphicon glyphicon\-([a-zA-Z\-]*) ?}/', '<span class="glyphicon glyphicon-${1}"></span>', $data);
	$data = preg_replace('/{ ?(fas|fa|fab) fa\-([a-zA-Z\-]*) ?}/', '<i class="${1} fa-${2}"></i>', $data);

	return $data;
}

function preg_color($data) {
	$data = preg_replace('/{ ?color:#([a-zA-Z0-9]{6}) ?}(.*){ ?\/color ?}/', '<span style="color: #${1}">${2}</span>', $data);

	return $data;
}

function upreg_menu_name($data) {
	$data = preg_replace('/<span class="glyphicon glyphicon\-([a-zA-Z\-]*)"><\/span>/', '{glyphicon glyphicon-${1}}', $data);
	$data = preg_replace('/<i class="(fas|fa|fab) fa\-([a-zA-Z\-]*)"><\/i>/', '{${1} fa-${2}}', $data);
	$data = preg_replace('/<font color="#([a-zA-Z0-9]{6})">(.*)<\/font>/', '{color:#${1}}${2}{/color}', $data);
	$data = preg_replace('/<(span|font) style="color: #([a-zA-Z0-9]{6})">(.*)<\/(span|font)>/', '{color:#${2}}${3}{/color}', $data);

	return $data;
}

function get_template($conf) {
	if(isset($_COOKIE['template']) && $_COOKIE['template'] != "" && $_COOKIE['template'] != "admin" && preg_match("/([a-zA-Z0-9]{1,50})/", $_COOKIE['template'])) {
		$template = check($_COOKIE['template'], null);
		$_SESSION['original_template'] = $conf->template;
	} else {
		unset($_SESSION['original_template']);
		if($conf->template != $conf->template_mobile) {
			$MD = new MobileDetect;
			if($MD->isMobile()) {
				$template = $conf->template_mobile;
			} else {
				$template = $conf->template;
			}
			unset($MD);
		} else {
			$template = $conf->template;
		}
	}

	return $template;
}

function check_function($func, $names) {
	$names = explode(',', $names);
	$func = check($func, null);
	if(isset($func) && in_array($func, $names)) {
		return $func;
	} else {
		return false;
	}
}

function return_html($data, $status, $show_icon) {
	if($show_icon == 2) {
		if($status == 1) {
			$data = '<span class="m-icon icon-ok"></span> '.$data;
		} elseif($status == 2) {
			$data = '<span class="m-icon icon-remove"></span> '.$data;
		}
	}

	if($status == 1) {
		$data = '<p class="text-success">'.$data.'</p>';
	} elseif($status == 2) {
		$data = '<p class="text-danger">'.$data.'</p>';
	}

	if($show_icon == 1) {
		if($status == 1) {
			$data = $data.'<script>setTimeout(show_ok, 500);</script>';
		} elseif($status == 2) {
			$data = $data.'<script>setTimeout(show_error, 500);</script>';
		}
	}

	exit($data);
}

function is_auth() {
	if(isset($_SESSION['id'])) {
		return true;
	} else {
		return false;
	}
}

function is_admin() {
	if(isset($_SESSION['id']) && is_worthy("h")) {
		return true;
	}
	
	return false;
}

function is_admin_id($id = 0) {
	global $main_admins;

	if($id === 0) {
		if(!is_auth()) {
			return false;
		}

		if(in_array($_SESSION['id'], $main_admins)) {
			return true;
		} else {
			return false;
		}
	} else {
		if(in_array($id, $main_admins)) {
			return true;
		} else {
			return false;
		}
	}
}

function is_worthy($access, $group = 0) {
	global $users_groups;

	if($group == 0 && array_key_exists('rights', $_SESSION)) {
		$group = $_SESSION['rights'];
	}

	if(strripos($users_groups[$group]['rights'], $access) !== false) {
		return true;
	} else {
		return false;
	}
}

function is_worthy_specifically($access, $refinement, $group = 0) {
	global $users_groups;

	if($group == 0 && array_key_exists('rights', $_SESSION)) {
		$group = $_SESSION['rights'];
	}

	if(preg_match('/'.$access.'([:0-9]*)[a-z]?/is', $users_groups[$group]['rights'], $matches)) {
		if($matches[1] == '') {
			return true;
		} else {
			$rights = explode(":", $matches[1]);
			if(in_array($refinement, $rights)) {
				return true;
			}
		}
	}

	return false;
}

function get_specifically_worthy($access, $group = 0) {
	global $users_groups;

	if($group == 0 && array_key_exists('rights', $_SESSION)) {
		$group = $_SESSION['rights'];
	}

	if(preg_match('/'.$access.'([:0-9]*)[a-z]?/is', $users_groups[$group]['rights'], $matches)) {
		if($matches[1] == '') {
			return true;
		} else {
			$rights = explode(":", $matches[1]);
			if(!is_array($rights)) {
				$rights = array(0 => $rights);
			}
			return $rights;
		}
	}

	return false;
}

function validateCaptcha($gRecaptchaResponse) {
	require_once __DIR__ . '/classes/ReCaptcha/loader.php';

	if(configs()->captcha == 1) {
		global $host;

		$recaptcha = new \ReCaptcha\ReCaptcha(configs()->captcha_secret);

		$resp = $recaptcha->setExpectedHostname($host)
			->verify($gRecaptchaResponse);
		if ($resp->isSuccess()) {
			return true;
		} else {
			return false;
		}
	}

	return true;
}

function isOnBlackList($pdo, $who, $whom)
{
	$STH = $pdo->prepare(
		"SELECT id FROM users__black_list WHERE whom=:whom AND who=:who LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':who' => $who, ':whom' => $whom]);
	$row = $STH->fetch();

	return empty($row->id) ? false : $row->id;
}

function isOnMyBlacklist($pdo, $userId)
{
	$result = false;

	if(is_auth()) {
		$result = isOnBlackList($pdo, $_SESSION['id'], $userId);
	}

	return $result;
}

function isOnHisBlacklist($pdo, $userId)
{
	$result = false;

	if(is_auth()) {
		$result = isOnBlackList($pdo, $userId, $_SESSION['id']);
	}

	return $result;
}


function isSomeKeyInArrayExists($keys, $array) {
	if(!is_array($keys)) {
		$keys = [$keys];
	}

	foreach($keys as $key) {
		if(array_key_exists($key, $array)) {
			return $key;
		}
	}

	return false;
}

if(!function_exists('array_column')) {
	function array_column(array $input, $columnKey, $indexKey = null)
	{
		$array = [];
		foreach($input as $value) {
			if(!array_key_exists($columnKey, $value)) {
				return false;
			}
			if(is_null($indexKey)) {
				$array[] = $value[$columnKey];
			} else {
				if(!array_key_exists($indexKey, $value)) {
					return false;
				}
				if(!is_scalar($value[$indexKey])) {
					return false;
				}
				$array[$value[$indexKey]] = $value[$columnKey];
			}
		}
		return $array;
	}
}

function get_update_servers($pdo, $server_id = null) {
	$sth = $pdo->query("SELECT * FROM `config__updates` WHERE 1");
	
	$servers = "";
	
	if($sth->rowCount()) {
		global $conf;
		
		if(isset($server_id))
			$conf->update_server = $server_id;
		
		$sth->setFetchMode(PDO::FETCH_OBJ);
		$servers = "<option value='0' " . (($conf->update_server <= 0) ? "selected" : "") . " disabled>- выбрать -</option>";
		
		while($row = $sth->fetch()) {
			$servers .= "<option value='{$row->id}' ".(($conf->update_server == $row->id) ? "selected" : "").">{$row->name}</option>";
		}
	}
	else {
		$servers = "<option value='0' selected disabled>- нет доступных -</option>";
	}
	
	return $servers;
}

function set_update_server($pdo, $server_id) {
	return $pdo->query("UPDATE `config` SET `update_server`='{$server_id}' WHERE 1");
}

function check_update_server($pdo, $server_id) {
	$sth = $pdo->query("SELECT * FROM `config__updates` WHERE `id`='{$server_id}'");
	$sth->setFetchMode(PDO::FETCH_OBJ);
	$row = $sth->fetch();
	
	return is_valid_site("https://" . $row->url);
}

function is_valid_site($domain = "google.com") {
	if(!filter_var($domain, FILTER_VALIDATE_URL)):
		return false;
	endif;
	
	$curlInit = curl_init($domain);
	curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curlInit, CURLOPT_HEADER, true);
	curl_setopt($curlInit, CURLOPT_NOBODY, true);
	curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($curlInit);
	curl_close($curlInit);
	
	if($response):
		return true;
	endif;
	
	return false;
}

function get_update_url($pdo) {
	global $conf;
	
	$sth = $pdo->query("SELECT * FROM `config__updates` WHERE `id`='{$conf->update_server}'");
	$sth->setFetchMode(PDO::FETCH_OBJ);
	$row = $sth->fetch();
	
	return $row->url;
}

function check_update_version($pdo, $version) {
	$remoteVersions = curl_get_process(['website' => "https://". get_update_url($pdo) ."/api?type=updates",'data' => '&product=gamecms']);
	$remoteVersions = json_decode(gzdecode($remoteVersions), true);
	
	for($i = 0; $i < sizeof($remoteVersions); $i++) {
		if($version == $remoteVersions[$i]['version']) {
			$index = $i;
			break;
		}
	}
	
	if(isset($remoteVersions[$index + 1]['version']) && $remoteVersions[$index]['version'] < $remoteVersions[$index + 1]['version']) {
		return ['status' => '0', 'versions' => $remoteVersions, 'index' => $index];
	}
	
	return ['status' => '1', 'versions' => $remoteVersions, 'index' => $index];
}

function curl_get_process($data = []) {
	$ch = curl_init($data['website']);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data['data']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$result = curl_exec($ch);
	curl_close($ch);
	
	return $result;
}

function download_file($data = []) {
	/*
		temp - дирекция, в которую будет загружаться файл.
		url - ссылка на файл, который нужно загрузить.
		file - название файла с расширением.
	*/
	if(isset($data['temp'])) {
		if(!file_exists("{$_SERVER['DOCUMENT_ROOT']}/{$data['temp']}")) {
			mkdir("{$_SERVER['DOCUMENT_ROOT']}/{$data['temp']}");
		}
	}
	
	$cInit = curl_init($data['url']);
	$fOpen = fopen("{$_SERVER['DOCUMENT_ROOT']}/{$data['temp']}/{$data['file']}", "wb");
	curl_setopt($cInit, CURLOPT_FILE, $fOpen);
	curl_setopt($cInit, CURLOPT_HEADER, 0);
	curl_exec($cInit);
	curl_close($cInit);
	
	/*
		status - отправляется статус закрытия файла (сохранение).
		file - отправляется дирекция с конечным файлом.
	*/
	return ['status' => fclose($fOpen), 'file' => "{$_SERVER['DOCUMENT_ROOT']}/{$data['temp']}/{$data['file']}"];
}

function importSqlFile($pdo, $sqlFile, $data = []) {
    try {
        $pdo->setAttribute(\PDO::MYSQL_ATTR_LOCAL_INFILE, true);
        $errorDetect = false;
        $tmpLine = '';
        $lines = file($sqlFile);
        
        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || trim($line) == '') {
                continue;
            }
			
            $line = str_replace(['<<project>>', '<<salt>>', '<<code>>'], [$data['project'], $data['salt'], $data['code']], $line);
            $tmpLine .= $line;
            
            if (substr(trim($line), -1, 1) == ';') {
                try {
                    // Perform the Query
                    $pdo->exec($tmpLine);
                } catch (\PDOException $e) {
                    echo "<br><pre>Error performing Query: '<strong>" . $tmpLine . "</strong>': " . $e->getMessage() . "</pre>\n";
                    $errorDetect = true;
                }
                
                $tmpLine = '';
            }
        }
        
        if ($errorDetect) {
            return false;
        }
        
    } catch (\Exception $e) {
        echo "<br><pre>Exception => " . $e->getMessage() . "</pre>\n";
        return false;
    }
    
    return true;
}

function isNeedHidePlayerId()
{
	global $conf;

	if($conf->hide_players_id == 1 || $conf->hide_players_id == 3) {
		return true;
	} else {
		return false;
	}
}

function isNeedHideAdminId()
{
	global $conf;

	if($conf->hide_players_id == 1 || $conf->hide_players_id == 2) {
		return true;
	} else {
		return false;
	}
}

function hidePlayerId($id)
{
	global $messages;

	if(
		(
			is_worthy('i')
			|| is_worthy('k')
			|| is_worthy('s')
			|| is_worthy('j')
		) || !SteamIDOperations::ValidateSteamID($id)
	) {
		return $id;
	} else {
		return $messages['isHidden'];
	}
}

if(
	!function_exists('random_bytes')
	|| !function_exists('random_int')
) {
	include_once __DIR__ . '/classes/Random/random.php';
}

function isStringLengthLess($string, $length)
{
	if(mb_strlen($string, 'UTF-8') < $length) {
		return true;
	} else {
		return false;
	}
}

function isStringLengthMore($string, $length)
{
	return !isStringLengthLess($string, $length);
}

function getPhpVersion()
{
	if(phpversion()) {
		$phpVersion = explode('.', phpversion());
	} else {
		$phpVersion = explode('.', PHP_VERSION);
	}

	return $phpVersion;
}

function pdo()
{
	global $pdo;

	return empty($pdo) ? new stdClass() : $pdo;
}

function configs()
{
	global $conf;

	return empty($conf) ? new stdClass() : $conf;
}

function page()
{
	global $page;

	return empty($page) ? new stdClass() : $page;
}

function tpl()
{
	global $tpl;

	if(empty($tpl)) {
		$tpl = new Template;
	}

	return $tpl;
}

function isRightToken()
{

	if(configs()->token == 1 && $_SESSION['token'] != $_POST['token']) {
		return false;
	} else {
		return true;
	}
}

function isPostRequest()
{
	if(empty($_POST) || !array_key_exists('phpaction', $_POST)) {
		return false;
	} else {
		return true;
	}
}

function token()
{
	global $token;

	return empty($token) ? '' : $token;
}

function HTMLPurifier()
{
	require_once __DIR__ . '/classes/HTMLPurifier/HTMLPurifier/Bootstrap.php';
	require_once __DIR__ . '/classes/HTMLPurifier/HTMLPurifier.autoload.php';

	$config = HTMLPurifier_Config::createDefault();
	$config->set('HTML.SafeIframe', true);
	$config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');

	$HTMLDefinition = $config->getHTMLDefinition(true);
	$HTMLDefinition->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
	$HTMLDefinition->addAttribute('iframe', 'allowfullscreen', 'Enum#allowfullscreen');

	foreach(['audio', 'video'] as $item) {
		$$item = $HTMLDefinition->addElement(
			$item,
			'Block',
			'Flow',
			'Common',
			['src*' => 'URI', 'controls' => 'CDATA']
		);

		$$item->excludes = [$item => true];
	}

	return new HTMLPurifier($config);
}

function incNotifications()
{
	include_once __DIR__ . '/notifications.php';
}

function dd($data)
{
	echo '<style>html, body { background: rgb(30, 30, 30); color: rgb(240, 240, 240); }</style>'
		. '<pre>'
		. var_export($data, true)
		. '</pre>';

	die;
}

function getNameLike($name)
{
	if(mb_strlen($name, 'UTF-8') < 3) {
		return $name;
	} else {
		return "%" . strip_data($name) . "%";
	}
}

function isMobile()
{
	if((new MobileDetect())->isMobile()) {
		return true;
	} else {
		return false;
	}
}

function get_user_status($id_user = null) {
	if(empty($id_user)):
		return null;
	endif;

	return pdo()->query("SELECT * FROM `users` WHERE `id`='{$id_user}'")->fetch(PDO::FETCH_OBJ)->status_message;
}

function SourceQuery() {
	global $SourceQuery;

	return isset($SourceQuery) ? $SourceQuery : null;
}