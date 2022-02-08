<?php

class AjaxResponse
{
	const HTML_RESPONSE = 'html';
	const JSON_RESPONSE = 'json';

	private $responseType = self::JSON_RESPONSE;
	private $errors = [];
	private $data = null;
	private $alert = null;
	private $status = true;

	function html() {
		$this->responseType = self::HTML_RESPONSE;

		return $this;
	}

	function json() {
		$this->responseType = self::JSON_RESPONSE;

		return $this;
	}

	function error($input, $message) {
		$this->errors[$input] = $message;
		$this->status = false;

		return $this;
	}

	public function status($status)
	{
		$this->status = $status;

		return $this;
	}

	public function data($data)
	{
		$this->data = $data;

		return $this;
	}

	public function alert($message)
	{
		$this->alert = $message;

		return $this;
	}

	function send() {
		if($this->responseType == self::JSON_RESPONSE) {
			$answer = [];
		} else {
			$answer = '';
		}

		if(!$this->status) {
			http_response_code(400);
		}

		if(!empty($this->alert)) {
			if($this->responseType == self::JSON_RESPONSE) {
				$answer['alert'] = $this->alert;
			} else {
				$answer .= 'Внимание! ' . $this->alert . '<br>';
			}
		}

		if(!empty($this->errors)) {
			if($this->responseType == self::JSON_RESPONSE) {
				$answer['errors'] = $this->errors;
			} else {
				foreach($this->errors as $input => $message) {
					$answer .= 'Ошибка в поле ' . $input . ': «' . $message . '» <br>';
				}
			}
		}

		if(!empty($this->data)) {
			if($this->responseType == self::JSON_RESPONSE) {
				$answer['data'] = $this->data;
			} else {
				$answer .= $this->data;
			}
		}

		if($this->responseType == self::JSON_RESPONSE) {
			exit(json_encode($answer));
		} else {
			exit($answer);
		}
	}
}