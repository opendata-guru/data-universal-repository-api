<?php
	function systems($url) {
		$suffix = '/api/v1/explore';

		$uri = $url . $suffix;
		$json = json_decode(get_contents_30sec($uri));

		$version = $json->version;
		$href = $url;

		echo json_encode((object) array(
			'extensions' => array(),
			'system' => 'sample',
			'url' => $href,
			'version' => $version,
		));
	}
?>