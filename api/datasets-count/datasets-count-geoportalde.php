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
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$ret = curl_exec($ch);
		curl_close ($ch);
		return $ret;
	}

	function datasetsCountGeoportalDE($url) {
		// $url must be 'https://www.geoportal.de'
		$url = 'https://www.geoportal.de';
		$uri = $url . '/es/metadata_all/_search';

		$query = '{'.
			'"query":{'.
				'"bool":{'.
					'"must":[{"match_all":{}}],'.
					'"should":['.
/*						'{"rank_feature":{"boost":20,"field":"monitor_ranking.title"}},'.
						'{"rank_feature":{"boost":20,"field":"monitor_ranking.bbox"}},'.
						'{"rank_feature":{"boost":20,"field":"monitor_ranking.abstract"}},'.
						'{"rank_feature":{"boost":20,"field":"monitor_ranking.keywords"}},'.
						'{"rank_feature":{"boost":20,"field":"monitor_ranking.contact"}},'.
						'{"rank_feature":{"boost":20,"field":"monitor_ranking.constraints"}},'.
						'{"rank_feature":{"boost":100,"field":"monitor_ranking.graphic"}},'.
						'{"rank_feature":{"boost":200,"field":"monitor_ranking.service_tests"}},'.
						'{"rank_feature":{"boost":200,"field":"monitor_ranking.metadata_tests"}}'.*/
					'],'.
					'"filter":['.
						'{"term":{"keyword":"OPEN DATA"}}'.
//						'{"term":{"keywords.keyword":"opendata"}}'.
					']'.
				'}'.
			'},'.
			'"aggs":{'.
/*				'"language":{"terms":{"field":"language","size":3}},'.
				'"service":{"terms":{"field":"service","size":2}},'.
				'"resourcetype":{"terms":{"field":"resourcetype","size":20}},'.
				'"keyword":{"terms":{"field":"keywords.keyword","exclude":[""],"size":5000}},'.
				'"inspireThemen":{"terms":{"field":"inspireThemen.keyword","size":100}},'.
				'"isoThemen":{"terms":{"field":"isoThemen.keyword","size":100}},'.
				'"datenanbieter":{"terms":{"field":"datenanbieter.keyword","size":5000}},'.
				'"katalogName":{"terms":{"field":"katalog_name","size":5000}},'.
				'"inspireumgesetzt":{"terms":{"field":"inspireumgesetzt","size":100}},'.
				'"servicetypeversion":{"terms":{"field":"service_version","size":20,"order":{"_key":"asc"}}},'.
				'"transferOptionsLinkType":{"terms":{"exclude":"Link","field":"transferOptions.LinkType","size":3}}'.*/
			'},'.
			'"from":0,'.
			'"size":10,'.
			'"track_total_hits":true'.
		'}';

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