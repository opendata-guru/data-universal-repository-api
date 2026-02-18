<?php
	function countDatasetsMCloud($url) {
		$searchUri = 'https://www.mcloud.de/web/guest/suche/';

		$source = file_get_contents($searchUri);

		$html = $source;
		$start = stripos($html, 'class="result-number"');
		$end = stripos($html, '<span', $start);
		$length = $end - $start;
		$html = trim(substr($html, $start, $length));
		$html = explode('>', $html);
		$packageCount = trim($html[1]);

		echo json_encode((object) array(
			'number' => intval($packageCount),
		));
	}
?>