<?php
	function postRequest($url, $payload) {
		$headers = [
			'Accept: */*',
			'Accept-Language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
			'Content-Type: application/json',
			'DNT: 1',
			'Origin: ' . $url,
			'Referer: https://geoportal.de/search.html?q=&filter.keyword=OPEN%20DATA&style=narrow',
			'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
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

	function datasetsCountGeoportalDE($url) {
		$uri = $url . '/es/metadata_all/_search';

		$query = '{'.
			'"query":{'.
				'"bool":{'.
					'"must":[{"match_all":{}}],'.
					'"should":[],'.
					'"filter":[{"term":{"keyword":"OPEN DATA"}}]'.
			'}},'.
			'"aggs":{'.
//				'"datenanbieter":{"terms":{"field":"datenanbieter.keyword","size":1}}'.
			'},'.
			'"from":0,'.
			'"size":0,'.
			'"track_total_hits":true}';

		$json = json_decode(postRequest($uri, $query));

		if ($json) {
			echo json_encode((object) array(
				'number' => $json->hits->total->value,
			));
		} else {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. The underlying system (geoportal.de) could not be detected',
			));
		}

	}
?>