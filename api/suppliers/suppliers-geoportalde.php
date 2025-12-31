<?php
	function suppliersGeoportalDE($url, $pid) {
//		$gdideSuffix = 'https://geoportal.de';
		$gdideSuffix = 'https://www.geoportal.de';
		$max = 30000;

		$uri = $gdideSuffix . '/es/metadata_all/_search';
		$uriDomain = end(explode('/',$gdideSuffix));

		function postRequest($url, $payload) {
			$headers = [
				'Accept: */*',
				'Accept-Language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
				'Content-Type: application/json',
				'DNT: 1',
				'Origin: ' . $gdideSuffix,
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

		$query = '{'.
			'"query":{'.
				'"bool":{'.
					'"must":[{"match_all":{}}],'.
					'"should":[],'.
					'"filter":[{"term":{"keyword":"OPEN DATA"}}]'.
			'}},'.
			'"aggs":{'.
				'"datenanbieter":{"terms":{"field":"datenanbieter.keyword","size":' . $max . '}}'.
			'},'.
			'"from":0,'.
			'"size":0,'.
			'"track_total_hits":true}';

		$json = json_decode(postRequest($uri, $query));

		$data = [];

		foreach($json->aggregations->datenanbieter->buckets as $organisation) {
//			if ($organisation->key > 5) {
				$name = preg_replace('#[^a-z0-9]#i', '', $organisation->key);
				$data[] = semanticContributor($uriDomain, $pid, array(
					'id' => $name,
					'name' => $name,
					'title' => $organisation->key,
					'created' => '',
					'packages' => $organisation->doc_count,
					'uri' => ''
				));
//			}
		}

		echo json_encode($data);
	}
?>