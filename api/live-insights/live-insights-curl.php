<?php
	function curl($url) {
		$headers = [
			'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
		];

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_FILETIME, true);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);

		$error = '';
		$content = curl_exec($curl);
		if (curl_errno($curl)){   
			$error = curl_error($curl);
		}
		$info = curl_getinfo($curl);
		curl_close($curl);

		$charset = '';
		$ct2 = [];
		if ($info['content_type']) {
			$ct = explode(';', $info['content_type']);
			foreach($ct as $value) {
				$pair = explode('=', $value);

				if (trim(strtolower($pair[0])) === 'charset') {
					$charset = trim(strtolower($pair[1]));
				} else {
					$ct2[] = $value;
				}
			}
		}

		$ret = (object) array(
			'content' => $content,
			'error' => $error,
			'metadata' => (object) array(
				'charset' => $charset,
				'contentType' => implode(';', $ct2),
				'effectiveMethod' => $info['effective_method'],
				'effectiveURL' => $info['url'],
				'fileTime' => $info['filetime'],
				'fileTimeISO' => $info['filetime'] === -1 ? null : date("Y-m-d H:i:s", $info['filetime']),
				'httpCode' => $info['http_code'],
				'sizeDownload' => $info['size_download'],
				'speedDownload' => $info['speed_download'],
				'totalTime' => $info['total_time'],
			),
			'url' => $url,
		);

		return $ret;
	}
?>