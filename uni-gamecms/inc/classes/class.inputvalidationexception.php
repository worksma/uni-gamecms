<?php

class InputValidationException extends Exception
{
	private $input;

	function setInput($name)
	{
		$this->input = $name;
	}

	function setMessage($message)
	{
		$this->message = $message;
	}

	public function getInput()
	{
		return $this->input;
	}
}