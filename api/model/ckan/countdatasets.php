<?php
	function countDatasetsEKAN($url) {
		$countWebsiteSuffix = '/search?f[0]=content_type%3Adataset.dataset';

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

	function scrapeWebsite($url) {
		$countWebsiteSuffix = '/search';

		$uri = $url . $countWebsiteSuffix;
		$source = file_get_contents($uri);

		$html = $source;

		$ekan = stripos($html, 'ekan-theme');
		if (false !== $ekan) {
			return countDatasetsEKAN($url);
		}

		$start = stripos($html, 'view-header');
		$end = stripos($html, '</div>', $start);
		$length = $end - $start;
		$html = trim(substr($html, $start, $length));
		$html = explode(' ', $html);
		$packageCount = $html[count($html) - 2];

		return intval($packageCount);
	}

	function countDatasets($url) {
		$packageShowSuffix_3 = '/api/3/action/package_search?rows=1&start=0';
//		$packageShowSuffix = '/api/action/package_search?rows=1&start=0';
		$packageShowSuffix = '/api/action/package_search?fq=(isopen%3A%22true%22)&rows=1';
		$packageShowAllSuffix = '/api/3/action/package_search';
		$resourcesShowLimitSuffix = '/api/3/action/current_package_list_with_resources?limit=1000';
		$resourcesShowSuffix = '/api/3/action/current_package_list_with_resources';
		$dkan2search = '/api/1/search?page-size=0';

		$json = json_decode(get_contents($url . $packageShowSuffix_3));
		$count = 0;

		if ($json && $json->result) {
			$count = $json->result->count;
		} else {
			$json = json_decode(get_contents($url . $packageShowSuffix));

			if ($json) {
				$count = $json->result->count;
			} else {
				$json = json_decode(get_contents($url . $packageShowAllSuffix));

				if ($json) {
					$count = $json->result->count;
				} else {
					$json = json_decode(get_contents($url . $resourcesShowLimitSuffix));

					if ($json && (count($json->result) > 0)) {
						$count = count($json->result[0]);
					} else {
						$json = json_decode(get_contents($url . $resourcesShowSuffix));

						if ($json) {
							$count = count($json->result);
						} else {
							$json = json_decode(get_contents($url . $dkan2search));

							if ($json) {
								$count = intval($json->total);
							} else {
								$count = scrapeWebsite($url);
							}
						}
					}
				}
			}
		}

		echo json_encode((object) array(
			'number' => $count,
		));
	}
?>