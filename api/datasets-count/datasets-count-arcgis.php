<?php
	function getSiteInjection($html) {
		$start = stripos($html, 'id="site-injection"') + 19;
		$end = stripos($html, '</script>', $start);
		$length = $end - $start;
		$html = substr($html, $start, $length);

		$start = stripos($html, '"') + 1;
		$end = strripos($html, '"');
		$length = $end - $start;
		$html = substr($html, $start, $length);

		return json_decode(urldecode($html));
	}

	function getGroups($injection) {
		$groups = $injection->site->data->catalog->groups;

		return implode(rawurlencode(', '), $groups);
	}

	function datasetsCountArcGIS($url) {
//		$html = file_get_contents($url);
		$html = get_contents($url);

		$injection = getSiteInjection($html);
		$domainInfo = $injection->site->domainInfo;

		$baseURI = 'https://hub.arcgis.com/api/search/v1/collections/dataset/items?filter=((group%20IN%20(GROUPS)))&limit=0';
		$groups = getGroups($injection);
		$uri = str_replace('GROUPS', $groups, $baseURI);

		if ($groups !== null) {
			$json = json_decode(get_contents($uri));

			echo json_encode((object) array(
				'number' => $json->numberMatched,
			));
		} else {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. The underlying system (ArcGIS) could not be detected',
			));
		}
	}
?>