<?php
	function countDatasets($url) {
		$suffix = '/freitextsuche';

		$uri = $url . $suffix;
		$html = get_contents_30sec($uri);

		$start = stripos($html, 'search-result-hits');
		$start = stripos($html, '>', $start) + 1;
		$end = stripos($html, '</strong>', $start);
		$length = $end - $start;
		$html = trim(substr($html, $start, $length));

		$count = intval($html);

		echo json_encode((object) array(
			'number' => $count,
		));
	}
?>