<?php
	function postRequest($url, $payload) {
		$headers = [
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,;q=0.8',
			'Accept-Language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
			'Cache-Control: no-cache',
			'Content-Type: application/json',
			'DNT: 1',
			'Host: flask.datenadler.de',
			'Origin: https://datenadler.de',
			'Referer: ttps://datenadler.de/',
			'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
			'Accept: application/json',
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$ret = curl_exec($ch);
		curl_close ($ch);
		return $ret;
	}

	function datasetsCountDatenadler($url) {
		$curl = 'https://flask.datenadler.de/solr_search';

		$query = '{'.
			'"q": "",'.
			'"sort": "score",'.
			'"start": 0,'.
			'"rows": 0,'.
			'"choices": {'.
				'"dct_publisher_facet": {},'.
				'"dcat_theme_facet": {},'.
				'"dct_license_facet": {},'.
				'"dct_format_facet": {},'.
				'"rdf_type": {}'.
			'}'.
		'}';

		$json = json_decode(postRequest($curl, $query));

		if ($json) {
			echo json_encode((object) array(
				'number' => $json->response->numFound,
			));
		} else {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. The underlying system (Datenadler) could not be detected',
			));
		}
	}
?>