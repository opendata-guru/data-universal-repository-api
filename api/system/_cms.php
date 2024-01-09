<?php
	$file = '';

	function getGenerator() {
		global $file;

		$head = explode('</head', $file)[0];
		$metaName = explode('name="generator"', $head)[1];

		if (null === $metaName) {
			$metaName = explode('name="Generator"', $head)[1];
		}

		if ($metaName) {
			$content = explode('content', $metaName)[1];
			$value = explode('"', $content)[1];
			return $value;
		}

		return null;
	}

	function getLiferay() {
		global $file;

		$txt = explode('Liferay.Browser', $file)[1];
		$val = explode('=', $txt)[1];
		$val = explode('};', $val)[0];
		$val = /*json_decode*/($val . '}');
		$val = null;

		return $val;
	}

	function getCMS($link) {
		global $file;

		$file = file_get_contents($link);
		$generator = getGenerator();

		if ($generator && ('ckan' === explode(' ', $generator)[0])) {
			$query = 'https://ckan.';
			if (substr($link, 0, strlen($query)) === $query) {
				$link = 'https://' . substr($link, strlen($query));

				$file = file_get_contents($link);
				$generator = getGenerator();
			}
		}

		if ($generator && ('ckan' === explode(' ', $generator)[0])) {
			$generator = null;
		}

		if ((null === $generator) && (1 < count(explode(' Liferay ', $file)))) {
			$generator = getLiferay();
		}

		if (null === $generator) {
			$generator = '';
		}

		return $generator;
	}
?>