<?php
	function systems($url) {
		$suffix = '/api/3/action/status_show';

		$uri = $url . $suffix;
		$html = get_contents_30sec($uri);
		$json = json_decode($html);
		$cms = getCMS($url);

		if ($json) {
			echo json_encode((object) array(
				'cms' => $cms,
				'extensions' => $json->result->extensions,
				'system' => 'ckan',
				'url' => $json->result->site_url,
				'version' => $json->result->ckan_version,
			));
		} else {
			$ekan = stripos($html, 'ekan-theme');

			echo json_encode((object) array(
				'cms' => $cms,
				'extensions' => null,
				'system' => false !== $ekan ? 'ekan' : ((substr($cms, 0, 6) === 'Drupal') ? 'dkan' : 'ckan'),
				'url' => $url,
				'version' => null,
			));
		}
	}
?>