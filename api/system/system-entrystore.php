<?php
	function getVersion($str) {
		$str = strstr($str, 'entryscape');
		$str = strstr($str, 'version');
		$str = strstr($str, '\'');
		$str = strstr($str, ',', true);

		return trim($str, '\'');
	}

	function getBaseUrl($str) {
		$str = strstr($str, 'baseUrl');
		$str = strstr($str, '\'');
		$str = strstr($str, ',', true);

		return trim($str, '\'');
	}

	function getEntryStore($str) {
		$str = strstr($str, 'entrystore');
		$str = strstr($str, 'repository');
		$str = strstr($str, '\'');
		$str = strstr($str, ',', true);

		return trim($str, '\'');
	}

	function getBundles($str) {
		$str = strstr($str, 'itemstore');
		$str = strstr($str, 'bundles');
		$str = ltrim(strstr($str, '['), '[');
		$str = strstr($str, ']', true);

		$ret = [];

		if ($str) {
			$arr = explode(',', $str);

			foreach($arr as $item) {
				$ret[] = trim(trim(trim($item), '\''), '"');
			}
		}

		return $ret;
	}

	function getDefaultBundles($str) {
		$str = strstr($str, 'itemstore');
		$str = strstr($str, '!defaultBundles');
		$str = ltrim(strstr($str, '['), '[');
		$str = strstr($str, ']', true);

		$ret = [];

		if ($str) {
			$arr = explode(',', $str);

			foreach($arr as $item) {
				if ('//' === substr(trim($item), 0, 2)) {
					$lines = explode("\n", $item);
					foreach($lines as $line) {
						$line = trim($line);
						if ($line && ('//' !== substr($line, 0, 2))) {
							$ret[] = trim(trim(trim($line), '\''), '"');
						}
					}
					// nope
				} else {
					$item = trim($item);
					if ($item) {
						$ret[] = trim(trim($item, '\''), '"');
					}
				}
			}
		}

		return $ret;
	}

	function systemEntryStore($url) {
		$versionHackSuffix = '/theme/local.js';

//		$js = file_get_contents($url . $versionHackSuffix);
		$js = get_contents($url . $versionHackSuffix);

		if ($js) {
			echo json_encode((object) array(
				'extensions' => array_merge(getDefaultBundles($js), getBundles($js)),
				'system' => 'entryscape',
				'url' => getEntryStore($js),
				'version' => getVersion($js),
			));
		} else {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. The underlying system (EntryStore) could not be detected',
			));
		}
	}
?>