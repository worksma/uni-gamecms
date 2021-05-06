<?php

class OurSourceQuery extends SourceQuery {

	public function get_server($pdo, $id, $type = 1) {
		if($type == 1) {
			$STH = $pdo->prepare("SELECT servers.type, servers.ip, servers.port, servers.rcon, servers.rcon_password, servers__commands.reload_admins FROM servers LEFT JOIN servers__commands ON servers__commands.server=servers.id WHERE servers.id=:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':id' => $id));
			$row = $STH->fetch();
		} else {
			$STH = $pdo->prepare("SELECT servers.type, servers.ip, servers.port, servers.rcon, servers.rcon_password, servers__commands.kick, servers__commands.ban, servers__commands.reload_admins FROM servers LEFT JOIN servers__commands ON servers__commands.server=servers.id WHERE servers.id=:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':id' => $id));
			$row = $STH->fetch();
		}
		if(empty($row->ip) || $row->rcon == 2 || empty($row->rcon_password)) {
			return false;
		} else {
			return $row;
		}
	}

	public function check_connect($ip, $port, $type) {
		if($type == 4) {
			return $this->Connect($ip, $port, 1, SourceQuery::SOURCE);
		} else {
			return $this->Connect($ip, $port, 1, SourceQuery::GOLDSOURCE);
		}
	}

	public function replace($name, $var, $command) {
		return str_replace("{".$name."}", '"'.$var.'"', $command);
	}

	public function reolad_admins($pdo, $server, $admin = 0) {
		$id = $server;
		if(!$server = $this->get_server($pdo, $server)) {
			return false;
		}
		$this->check_connect($server->ip, $server->port, $server->type);
		$this->SetRconPassword($server->rcon_password);
		$this->Rcon($this->replace('id', $admin, $server->reload_admins));
		$this->log($server->reload_admins, $id);
		$this->Disconnect();
	}

	public function log($command, $server) {
		$file = get_log_file_name("rcon_log_".$server);

		if(file_exists($_SERVER['DOCUMENT_ROOT']."/logs/".$file)) {
			$i = "a";
		} else {
			$i = "w";
		}

		if(isset($_SESSION['id']) and isset($_SESSION['login'])) {
			$user = $_SESSION['login'].' - '.$_SESSION['id'];
		} else {
			$user = 'Админ Центр';
		}

		$file = fopen($_SERVER['DOCUMENT_ROOT']."/logs/".$file, $i);
		fwrite($file, "[".date("Y-m-d H:i:s")." | Пользователь: ".$user."] : [Команда: ".$command."] \r\n");
		fclose($file);
	}
}