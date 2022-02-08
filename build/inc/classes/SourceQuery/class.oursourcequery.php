<?php

class OurSourceQuery extends SourceQuery {

	private $server = null;

	public function setServer($server) {
		$this->server = $server;

		return $this;
	}

	public function isServerCanWorkWithRcon() {
		if(
			empty($this->server->id)
			|| $this->server->rcon == 2
			|| empty($this->server->rcon_password)
		) {
			return false;
		} else {
			return true;
		}
	}

	public function checkConnect() {
		if($this->server->game == 'Counter-Strike: 1.6') {
			$this->Connect($this->server->ip, $this->server->port, 1, SourceQuery::GOLDSOURCE);
		} else {
			$this->Connect($this->server->ip, $this->server->port, 1, SourceQuery::SOURCE);
		}

		return $this;
	}

	public function auth() {
		$this->SetRconPassword($this->server->rcon_password);

		return $this;
	}

	public function send($command) {
		$answer = $this->Rcon($command);
		$this->log($command);

		return $answer;
	}

	public function reloadAdmins($server = null) {
		if(is_null($this->server)) {
			$this->setServer(
				(new ServersManager())->getServer($server)
			);
		}

		$this->checkConnect();
		$this->auth();

		$command = (new ServerCommands())
			->getCommandBySlug(
				ServerCommands::RELOAD_ADMINS_COMMAND_SLUG,
				$this->server->id
			);

		$command = empty($command->command) ? '' : $command->command;

		$answer = $this->send($command);
		$this->Disconnect();

		return $answer;
	}

	public function log($command)
	{
		$file = get_log_file_name("rcon_log_" . $this->server->id);

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

		$file = fopen($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file, $i);
		fwrite(
			$file,
			"[" . date("Y-m-d H:i:s") . " | Пользователь: " . $user . "] : [Команда: " . clean($command, null) . "] \r\n"
		);
		fclose($file);
	}
}