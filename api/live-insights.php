<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

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
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);
		curl_setopt($curl, CURLOPT_FILETIME, true);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 10);

		$error = '';
		$content = curl_exec($curl);
		if (curl_errno($curl)){   
			$error = curl_error($curl);
		}
		$info = curl_getinfo($curl);
		curl_close($curl);

		$ret = (object) array(
			'error' => $error,
			'url' => $url,
			'effectiveMethod' => $info['effective_method'],
			'effectiveURL' => $info['url'],
			'contentType' => $info['content_type'],
			'httpCode' => $info['http_code'],
			'requestSize' => $info['request_size'],
			'fileTime' => $info['filetime'],
			'totalTime' => $info['total_time'],
			'sizeDownload' => $info['size_download'],
			'speedDownload' => $info['speed_download'],
			'content' => $content,
		);

		return $ret;
	}

	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	}

	$parameterURL = trim(htmlspecialchars($_GET['url']));

	if ($parameterURL == '') {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Parameter \'url\' is not set',
		));
		exit;
	}

	$content = curl($parameterURL);

	$ret = array();

	$ret = (object) array(
		'comming' => 'soon',
		'metadata' => $content,
		'content' => $content->content,
	);

	echo json_encode($ret);
?>