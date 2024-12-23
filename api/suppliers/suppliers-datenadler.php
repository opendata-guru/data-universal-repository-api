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
			'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
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

	function suppliersDatenadler($url, $pid) {
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

		$uriDomain = end(explode('/', $url));

		$data = [];

		$json = json_decode(postRequest($curl, $query));

		if ($json) {
			$list = $json->facets->dct_publisher_facet->buckets;

			for ($l = 0; $l < count($list); ++$l) {
				$item = $list[$l];

				$name = preg_replace('#[^a-z0-9]#i', '', $item->val);
				$data[] = semanticContributor($uriDomain, $pid, array(
					'id' => $name,
					'name' => $name,
					'title' => $item->val,
					'created' => '',
					'packages' => $item->count,
					'uri' => ''
				));
			}

			echo json_encode($data);
		} else {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. The underlying system (datenadler) could not be detected',
			));
		}
	}
?>