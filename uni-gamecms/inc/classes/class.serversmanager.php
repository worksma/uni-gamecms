<?php

class ServersManager
{
	private $pdo;

	function switch_game($game)
	{
		switch($game) {
			case 0:
				return "Counter-Strike: 1.6";
			case 1:
				return "Counter-Strike: Source";
			case 2:
				return "Counter-Strike: Global Offensive";
			case 3:
				return "Alien Swarm";
			case 4:
				return "CSPromod";
			case 5:
				return "Day of Defeat: Source";
			case 6:
				return "Dystopia";
			case 7:
				return "E.Y.E: Divine Cybermancy";
			case 8:
				return "Fortress Forever";
			case 9:
				return "Garry's Mod";
			case 10:
				return "Half-Life 2 Deathmatch";
			case 11:
				return "Half-Life 2 Capture the Flag";
			case 12:
				return "Hidden: Source";
			case 13:
				return "Insurgency: Source";
			case 14:
				return "Left 4 Dead 2";
			case 15:
				return "Left 4 Dead";
			case 16:
				return "Nuclear Dawn";
			case 17:
				return "Perfect Dark: Source";
			case 18:
				return "Pirates Vikings and Knights II";
			case 19:
				return "Team Fortress 2";
			case 20:
				return "The Ship";
			case 21:
				return "Zombie Panic";
		}
	}

	function check_types($type, $st_type)
	{
		if(!in_array($type, [0, 1, 2, 3, 4, 5, 6]) and !in_array(
				$st_type,
				[0, 1, 2, 3]
			)) {
			return false;
		} else {
			return true;
		}
	}

	function ftp_connection($ftp_host, $ftp_port, $ftp_login, $ftp_pass, $title)
	{
		$ftp_connection = ftp_connect($ftp_host, $ftp_port);
		if(!$ftp_connection) {
			log_error(
				$title . ': Не удалось подключиться к FTP серверу ' . $ftp_host . ':' . $ftp_port
			);
			return false;
		}
		$ftp_login = ftp_login($ftp_connection, $ftp_login, $ftp_pass);
		if(!$ftp_login) {
			log_error(
				$title . ': Не удалось авторизоваться на FTP сервере ' . $ftp_host . ':' . $ftp_port
			);
			return false;
		}
		ftp_pasv($ftp_connection, true);
		return $ftp_connection;
	}

	function close_ftp($ftp_connection)
	{
		ftp_close($ftp_connection);
		return true;
	}

	function explode_users_file($string)
	{
		preg_match("~(.+/)~ui", $string, $parse);
		if(isset($parse[1])) {
			$ftp['string'] = $parse[1];
		} else {
			$ftp['string'] = '';
		}

		$parse = explode("/", $string);
		if($parse[count($parse) - 1]) {
			$ftp['file'] = $parse[count($parse) - 1];
		} else {
			$ftp['file'] = '';
		}

		return $ftp;
	}

	function check_users_file($ftp_string)
	{
		$parse = $this->explode_users_file($ftp_string);
		if($parse['file'] == '' || $parse['string'] == '') {
			return false;
		} else {
			return true;
		}
	}

	function find_users_file($ftp_connection, $ftp_string)
	{
		$parse      = $this->explode_users_file($ftp_string);
		$ftp_file   = $parse['file'];
		$ftp_string = $parse['string'];

		$files = ftp_nlist($ftp_connection, $ftp_string);
		for($i = 0; $i <= count($files); $i++) {
			if(isset($files[$i]) && (($files[$i] == $ftp_string . $ftp_file) || ($files[$i] == $ftp_string . '/' . $ftp_file) || ($files[$i] == $ftp_file))) {
				return true;
			}
		}
		return false;
	}

	public function getServer($id) {
		$STH = pdo()->prepare("SELECT * FROM servers WHERE id=:id LIMIT 1");
		$STH->execute([':id' => $id]);

		return $STH->fetch(PDO::FETCH_OBJ);
	}

	public function getList() {
		return pdo()->query("SELECT * FROM servers ORDER BY trim")->fetchAll(PDO::FETCH_OBJ);
	}
}