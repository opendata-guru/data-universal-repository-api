<?php
	function getVersion($str) {
		$str = strstr($str, 'opendata-ui version');
		$str = strstr($str, ':');
		$str = strstr($str, '-->', true);

		return trim(trim($str, ':'));
	}

	function systemArcGIS($url) {
//		$html = file_get_contents($url);
		$html = get_contents($url);

		if ($html) {
			echo json_encode((object) array(
				'extensions' => null,
				'system' => 'ArcGIS Hub',
				'url' => $url,
				'version' => getVersion($html),
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