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

	// wrong
	// https://geo.sv.rostock.de/inspire/plu-localplans/download
	// https://geo.sv.rostock.de/inspire/plu-localplans/view
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/download
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/view
	// right
	// https://geo.sv.rostock.de/inspire/plu-localplans/download?service=WFS&version=2.0.0&request=GetCapabilities
	// https://geo.sv.rostock.de/inspire/plu-localplans/view?service=WMS&version=1.3.0&request=GetCapabilities
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/download?service=WFS&version=2.0.0&request=GetCapabilities
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/view?service=WMS&version=1.3.0&request=GetCapabilities

	// opengis OWS
	function parseOWS($xml, &$body, &$error, &$contentType) {
		$rootName = $xml->getName();

		if ('ExceptionReport' === $rootName) {
			$xml->rewind();

			$key = $xml->key();
			$attributes = ((array) $xml->current())['@attributes'];
			$values = [];

			foreach($xml->getChildren() as $name => $data) {
				$values[] = '' . $data;
			}

			$error = (object) array(
				'type' => $rootName,
				'name' => $key,
				'code' => $attributes['exceptionCode'],
				'descriptions' => $values,
			);
			return;
		}

		$attributes = ((array) $xml)['@attributes'];
		$version = $attributes['version'];

		$ret = [];

		if ('WFS_Capabilities' === $rootName) {
			$contentType = 'ogc:wfs';
			$ret['version'] = $version;
		}

		for ($xml->rewind(); $xml->valid(); $xml->next()) {
			$key = $xml->key();
			$ret[] = $key;
//			$ret[] = $xml->current();
		}

		$body = (object) $ret;
	}

	function parser($file) {
		$MAGIC_XML = '<?xml ';
		$contentType = '';
		$body = null;
		$error = null;

		if ($MAGIC_XML === strtolower(substr($file->content, 0, strlen($MAGIC_XML)))) {
			$contentType = 'xml';
			$xml = simplexml_load_string($file->content);
			$ns = $xml->getDocNamespaces();
			if (in_array('http://www.opengis.net/ows/1.1', $ns)) {
				parseOWS($xml, $body, $error, $contentType);
			} else {
	//			$body = $xml->getName();
	//			$body = $xml->getNamespaces();
	//			$body = $xml->getChildren();
	//			$body = $xml['ows:Exception'];

	/*			$xml = new SimpleXMLElement($file->content);
				for ($xml->rewind(); $xml->valid(); $xml->next()) {
					foreach($xml->getChildren() as $name => $data) {
					echo "The $name is '$data' from the class " . get_class($data) . "\n";
					}
				}*/

	/*			try {
					$dom = new DOMDocument();
					$dom->loadXML($file->content);
					$body = $dom;
				} catch(Exception $e) {
					$body = $e;
				}*/
			}
		}

		$ret = (object) array(
			'contentType' => $contentType,
			'error' => $error,
			'body' => $body,
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

	$parameterURL = html_entity_decode(trim(htmlspecialchars($_GET['url'])));

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
	unset($content->content);

	$ret = (object) array(
		'file' => $content,
		'content' => $parsed,
	);

	echo json_encode($ret);
?>