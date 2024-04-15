<?php
	function postRequest($url, $payload) {
		$headers = [
			'Accept: application/json, text/plain, */*',
			'Content-Type: application/json',
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

	function suppliersMobilithek($url) {
		$aggregateSuffix = '/mdp-api/mdp-msa-metadata/v2/offers/aggregate';

		$uriDomain = explode('/', $url)[2];
		$uri = $url . $aggregateSuffix;
		$payload = '{' .
			'"filters":{' .
				'"searchString":""' .
			'},' .
			'"aggregationFields":[]' .
		'}';

		$json = json_decode(postRequest($uri, $payload));
		$data = [];

		foreach($json->publishers as $obj) {
			$key = array_keys(get_object_vars($obj))[0];
			$value = get_object_vars($obj)[$key];
			$id = preg_replace('#[^a-z0-9-]#i', '', $key);

			$data[] = semanticContributor($uriDomain, array(
				'id' => $id,
				'name' => $key,
				'title' => $key,
				'created' => '',
				'packages' => $value,
				'uri' => ''
			));
		}

		echo json_encode($data);
	}
?>