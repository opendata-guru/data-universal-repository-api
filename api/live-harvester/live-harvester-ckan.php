<?php
	function analyseLink($html, $url) {
		$start = stripos($html, '<a');
		$end = stripos($html, '</a>', $start);
		$length = $end - $start;
		$html = trim(substr($html, $start, $length));

		$start = stripos($html, 'href="') + 6;
		$end = stripos($html, '"', $start);
		$length = $end - $start;
		$href = trim(substr($html, $start, $length));

		$start = stripos($html, '>') + 1;
		$title = trim(substr($html, $start));

		return ['href' => $url . $href, 'title' => $title];
	}

	function analyseHarvester($harvester) {
		$uri = $harvester['href'];
		$uri = str_replace('/harvest/', '/harvest/about/', $uri);

		$source = file_get_contents($uri);

		$html = $source;
		$start = stripos($html, 'dataset-details');
		$end = stripos($html, '</td>', $start);
		$length = $end - $start;
		$source = trim(substr($html, $start, $length));

		return analyseLink($source, '');
	}

	function scrapeWebsiteList($url) {
		$ret = [];
		$page = 1;

		do {
			$listWebsiteSuffix = '/harvest/?page=' . $page;
			$uri = $url . $listWebsiteSuffix;
			$source = file_get_contents($uri);

			$html = $source;
			$start = stripos($html, 'dataset-list');
			if (false === $start) {
				return $ret;
			}

			$end = stripos($html, '</ul>', $start);
			$length = $end - $start;
			$html = trim(substr($html, $start, $length));

			$li = explode('</li>', $html);
			foreach($li as $entry) {
				if (trim($entry) !== '') {
					$start = stripos($entry, '<h3');
					$end = stripos($entry, '</h3>', $start);
					$length = $end - $start;
					$harvester = analyseLink(trim(substr($entry, $start, $length)), $url);

					$start = stripos($entry, 'class="muted"');
					$end = stripos($entry, '</p>', $start);
					$length = $end - $start;
					$organisation = analyseLink(trim(substr($entry, $start, $length)), $url);

					$source = analyseHarvester($harvester);

					$ret[] = [
						'id' => end(explode('/', $organisation['href'])),
						'source' => $source['href'],
						'title' => $organisation['title'],
					];
				}
			}

			++$page;
		} while($page < 10);

		return $ret;
	}

	function liveHarvesterCKAN($url) {
		$harvester = scrapeWebsiteList($url);

		echo json_encode((object) array(
			'work_in_progress' => $harvester,
		));
	}
?>