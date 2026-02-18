<?php
	function scrapeWebsite($url) {
		$countWebsiteSuffix = '/search';

		$uri = $url . $countWebsiteSuffix;
		$source = file_get_contents($uri);

		$html = $source;
		$start = stripos($html, 'view-header');
		$end = stripos($html, '</div>', $start);
		$length = $end - $start;
		$html = trim(substr($html, $start, $length));
		$html = explode(' ', $html);
		$packageCount = $html[count($html) - 2];

		return intval($packageCount);
	}

	function countDatasetsSpain($url) {
		$catalogSuffix = '/en/catalogo?q=';

		$count = 0;

		$uri = $url . $catalogSuffix;
		$source = file_get_contents($uri);

		$html = $source;
		$header = explode('<h1>', $html);
		$header = explode('</h1>', $header[1]);
		$title = trim($header[0]);
		$count = explode(' ', $title)[0];
		$count = str_replace(',', '', $count);

		echo json_encode((object) array(
			'number' => intval($count),
		));
	}
?>