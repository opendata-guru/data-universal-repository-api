<?php
	function systems($url) {
		// https://docs.conterra.de/de/mapapps/latest/developersguide/explanation/apprt/apprt.html
		$suffix = '';

		$uri = $url . $suffix;
		$html = get_contents_30sec($uri);

		$start = stripos($html, '$apprt.startApp');
		$start = stripos($html, '{', $start);
		$end = stripos($html, '}', $start) + 1;
		$length = $end - $start;
		$html = trim(substr($html, $start, $length));

		$html = preg_replace('/(\w+):/i', '"\1":', $html);
		$json = json_decode($html);

		$param = $json->param; // default: 'app'
		$app = $json->defaultApp;

//		$suffix = '/js/apps/' . $app . '/app.json?lang=de&app=' . $app;
//		$uri = $url . $suffix;
//		$html = get_contents_30sec($uri);

		$version = $json->version;
		$href = $url;

		echo json_encode((object) array(
			'extensions' => array($app),
			'system' => 'conterra',
			'url' => $href,
			'version' => $version,
		));
	}
?>