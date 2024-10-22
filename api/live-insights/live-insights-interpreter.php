<?php
	// test URLs:
	// https://www.inspire.niedersachsen.de/doorman/noauth/alkis-dls-elu?REQUEST=GetCapabilities&SERVICE=WFS

	$semRegistry = array(
		(object) array(
			'id' => 'elu:ExistingLandUseObject',
			'uri' => 'https://inspire.ec.europa.eu/schemas/elu/4.0/ExistingLandUse.xsd',
			'title' => 'existing land use object',
			'definition' => 'An existing land use object describes the land use of an area having a homogeneous combination of land use types.',
		),
	);

	function interpret($file, $content) {
		global $semRegistry;

		$error = null;
		$assets = [];

		if ($content && $content->body) {
			if ($content && $content->body->assets) {
				foreach($content->body->assets as $asset) {
					foreach($semRegistry as $entry) {
						if ($entry->id === $asset->name) {
							$assets[] = (object) array(
								'id' => $entry->uri,
								'title' => $entry->title,
							);
						}
					}
				}
			}
		}

		$ret = (object) array(
			'error' => $error,
			'assets' => $assets,
		);

		return $ret;
	}
?>