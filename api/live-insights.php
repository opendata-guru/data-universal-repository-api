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

		$charset = '';
		$ct = explode(';', $info['content_type']);
		$ct2 = [];
		foreach($ct as $value) {
			$pair = explode('=', $value);

			if (trim(strtolower($pair[0])) === 'charset') {
				$charset = trim(strtolower($pair[1]));
			} else {
				$ct2[] = $value;
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

	function parser($file) {
		$MAGIC_XML = '<?xml ';
		$contentType = '';
		$content = '';

		if ($MAGIC_XML === strtolower(substr($file->content, 0, strlen($MAGIC_XML)))) {
			$contentType = 'xml';
			$xml = simplexml_load_string($file->content);
			$content = $xml->children();
//			$content = $xml['ows:Exception'];

/*			try {
				$dom = new DOMDocument();
				$dom->loadXML($file->content);
				$content = $dom;
			} catch(Exception $e) {
				$content = $e;
			}*/
		}

		$ret = (object) array(
			'contentType' => $contentType,
			'content' => $content,
		);
/*
<?xml version='1.0' encoding='UTF-8'?>\n
  <ows:ExceptionReport xmlns:ows=\"http://www.opengis.net/ows/1.1\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.opengis.net/ows/1.1 http://schemas.opengis.net/ows/1.1.0/owsExceptionReport.xsd\" version=\"2.0.0\">\n  
    <ows:Exception exceptionCode=\"MissingParameterValue\">\n
	  <ows:ExceptionText>The request did not contain any parameters.</ows:ExceptionText>\n
	</ows:Exception>\n
  </ows:ExceptionReport>",
*/
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
	$parsed = parser($content);

	$ret = array();

	$ret = (object) array(
		'comming' => 'soon',
		'file' => $content,
		'content' => $parsed,
	);

	echo json_encode($ret);
?>