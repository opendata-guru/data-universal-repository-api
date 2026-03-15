<?php
	function liveSystemCKAN($url) {
		$statusShowSuffix = '/api/3/action/status_show';

		$html = get_contents_30sec($url . $statusShowSuffix);
		$json = json_decode($html);
		$cms = getCMS($url);

		$ekan = stripos($html, 'ekan-theme');

		if ($json) {
			echo json_encode((object) array(
				'cms' => $cms,
				'extensions' => $json->result->extensions,
				'system' => 'CKAN',
				'url' => $json->result->site_url,
				'version' => $json->result->ckan_version,
			));
		} else {
			echo json_encode((object) array(
				'cms' => $cms,
				'extensions' => null,
				'system' => false !== $ekan ? 'EKAN' : ((substr($cms, 0, 6) === 'Drupal') ? 'DKAN' : 'CKAN'),
				'url' => $url,
				'version' => null,
			));
		}
	}
?>