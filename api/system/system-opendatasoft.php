<?php
	function systemOpendatasoft($url) {
		$apiExplore = '/api/explore/';

		$json = json_decode(get_contents($url . $apiExplore));
		$version = $json->current_version;

		$href = $url;
		foreach ($json->links as $link) {
			if ($link->rel === $version) {
				$href = $link->href;
			}
		}

		echo json_encode((object) array(
			'extensions' => array(),
			'system' => 'Opendatasoft',
			'url' => $href,
			'version' => $version,
		));
	}
?>