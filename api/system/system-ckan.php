<?php
	function systemCKAN($url) {
		$statusShowSuffix = '/api/3/action/status_show';

//		$json = json_decode(file_get_contents($url . $statusShowSuffix));
		$json = json_decode(get_contents($url . $statusShowSuffix));
		$cms = getCMS($url);

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
				'system' => (substr($cms, 0, 6) === 'Drupal') ? 'DKAN' : null,
				'url' => $url,
				'version' => null,
			));
		}
	}
?>