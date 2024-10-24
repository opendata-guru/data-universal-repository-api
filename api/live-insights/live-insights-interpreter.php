<?php
	// test URLs:
	// https://www.inspire.niedersachsen.de/doorman/noauth/alkis-dls-elu?REQUEST=GetCapabilities&SERVICE=WFS

	$semRegistry = array(
//		'uri' => 'https://inspire.ec.europa.eu/featureconcept/ExistingLandUseDataSet',
		(object) array(
			'ids' => ['elu:ExistingLandUseObject'],
			'uri' => 'https://inspire.ec.europa.eu/schemas/elu/4.0/ExistingLandUse.xsd',
			'title' => 'existing land use object',
			'title_de' => 'Existierende Bodennutzung',
			'definition' => 'An existing land use object describes the land use of an area having a homogeneous combination of land use types.',
		),
		(object) array(
			'ids' => ['GE.GeologicFault'],
			'uri' => 'https://inspire.ec.europa.eu/layer/GE.GeologicFault',
			'title' => 'Geologic faults',
			'title_de' => 'Geologische Verwerfungen',
			'definition' => 'This layer only applies to MappedFeature spatial objects whose specification property is of type ShearDisplacementStructure.',
		),
		(object) array(
			'ids' => ['hy:DrainageBasin', 'hy-p:DrainageBasin'],
			'uri' => 'https://inspire.ec.europa.eu/featureconcept/DrainageBasin',
			'title' => 'Drainage Basin',
			'title_de' => 'Wassereinzugsgebiet',
			'definition' => 'Area having a common outlet for its surface runoff.',
		),
	);

	function interpret($file, $content) {
		global $semRegistry;

		$error = null;
		$assets = [];

		if ($content && $content->body) {
			$bodyAssets = $content->body->assets ? $content->body->assets : (property_exists($content->body,'assets') ? $content->body['assets'] : null);
			if ($bodyAssets) {
				foreach($bodyAssets as $asset) {
					foreach($semRegistry as $entry) {
						if ($asset->name && in_array($asset->name, $entry->ids)) {
							$assets[] = (object) array(
								'id' => $entry->uri,
								'title' => (object) ['de' => $entry->title_de, 'en' => $entry->title],
							);
						}

						foreach($entry->ids as $id) {
							if (str_contains($asset->title, $id)) {
								$assets[] = (object) array(
									'id' => $entry->uri,
									'title' => (object) ['de' => $entry->title_de, 'en' => $entry->title],
								);
							}
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