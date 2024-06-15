<?php
	function getBearerToken() {
		$headers = null;

		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER['Authorization']);
		} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$headers = trim($_SERVER['HTTP_AUTHORIZATION']);
		} else if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
			$headers = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
		}

		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}

	function validPost() {
		$valid = array('foo', 'bar');
		$token = getBearerToken();

		return in_array($token, $valid);
	}
?>