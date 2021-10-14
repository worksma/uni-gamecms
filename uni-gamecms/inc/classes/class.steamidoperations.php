<?php

class SteamIDOperations
{
	private $firstNum = 1;

	public function setFirstNum($firstNum)
	{
		$this->firstNum = $firstNum;

		return $this;
	}

	public function is3($steamid)
	{
		if(preg_match("/^(\[U:1:[0-9]+\])$/", $steamid)) {
			return true;
		} else {
			return false;
		}
	}

	public function is32($steamid)
	{
		if(preg_match("/^(STEAM_[0-9]{1,2}:[01]:\d+|VALVE_[0-9]{1,2}:[0-1]:\d+)$/", $steamid)) {
			return true;
		} else {
			return false;
		}
	}

	public function is64($steamid)
	{
		if(preg_match("/^([0-9]{17})$/", $steamid)) {
			return true;
		} else {
			return false;
		}
	}

	public static function ValidateSteamID($steamid)
	{
		if(preg_match(
			"/^(STEAM_[0-9]{1,2}:[01]:\d+|VALVE_[0-9]{1,2}:[0-1]:\d+|\[U:1:[0-9]+\]|[0-9]{17})$/",
			$steamid
		)) {
			return true;
		} else {
			return false;
		}
	}

	public function GetSteamID32($steamid)
	{
		if(!self::ValidateSteamID($steamid)) {

			return false;
		}

		if($this->is3($steamid)) {
			return $this->SteamID3to32($steamid);
		}
		if($this->is64($steamid)) {
			return $this->SteamID64to32($steamid);
		}
		if($this->is32($steamid)) {
			return $steamid;
		}

		return false;
	}

	private function SteamID32to3($steamid32)
	{
		if(preg_match('/^STEAM_1\:*\:(.*)$/', $steamid32, $res)) {

			$st = '[U:1:';
			$st .= $res[1] * 2 + 1;
			$st .= ']';
			return $st;
		}
		return false;
	}

	private function SteamID3to32($steamid3)
	{
		if(preg_match("/\[U:1:(\d+)\]/", $steam3)) {
			$steam3 = preg_replace("/\[U:1:(\d+)\]/", "$1", $steamid3);
			$A      = $steam3 % 2;
			$B      = intval($steam3 / 2);
			return "STEAM_1:" . $A . ":" . $B;
		}
		return false;
	}

	private function SteamID32to64($steamid32)
	{
		if(preg_match('/^STEAM_1\:*\:(.*)$/', $steamid32, $res)) {
			list(, $m1, $m2) = explode(':', $steamid32, 3);
			list($steam_cid,) = explode('.', bcadd((((int)$m2 * 2) + $m1), '76561197960265728'), 2);
			return $steam_cid;
		}
		return false;
	}

	private function SteamID64to32($steamid64)
	{
		$pattern = "/^(7656119)([0-9]{10})$/";
		if(preg_match($pattern, $steamid64, $match)) {
			$const1  = 7960265728;
			$const2  = "STEAM_" . $this->firstNum . ":";
			$steam32 = '';
			if($const1 <= $match[2]) {
				$a       = ($match[2] - $const1) % 2;
				$b       = ($match[2] - $const1 - $a) / 2;
				$steam32 = $const2 . $a . ':' . $b;
			}
			return $steam32;
		}
		return false;
	}
}