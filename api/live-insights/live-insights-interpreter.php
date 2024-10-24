<?php
	// test URLs:
	// https://www.inspire.niedersachsen.de/doorman/noauth/alkis-dls-elu?REQUEST=GetCapabilities&SERVICE=WFS

	$semRegistry = array(
		(object) array(
			'ids' => ['au:AdministrativeBoundary'],
			'uri' => 'https://inspire.ec.europa.eu/featureconcept/AdministrativeBoundary',
			'title' => 'Administrative Boundary',
			'title_de' => 'Verwaltungsgrenze',
			'definition' => 'A line of demarcation between administrative units.',
		),
		(object) array(
			'ids' => ['au:AdministrativeUnit'],
			'uri' => 'https://inspire.ec.europa.eu/featureconcept/AdministrativeUnit',
			'title' => 'Administrative Unit',
			'title_de' => 'Verwaltungseinheit',
			'definition' => 'Unit of administration where a Member State has and/or exercises jurisdictional rights, for local, regional and national governance.',
		),
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
		(object) array(
			'ids' => ['HY.PhysicalWaters.Catchments'],
			'uri' => 'https://inspire.ec.europa.eu/layer/HY.PhysicalWaters.Catchments',
			'title' => 'Catchments',
			'title_de' => 'Einzugsgebiete',
			'definition' => '',
			'spatialobjecttype' => 'DrainageBasin',
		),
		(object) array(
			'ids' => ['hy-p:Watercourse'],
			'uri' => 'https://inspire.ec.europa.eu/featureconcept/Watercourse',
			'title' => 'Watercourse',
			'title_de' => 'Wasserlauf',
			'definition' => 'A natural or man-made flowing watercourse or stream.',
		),
		(object) array(
			'ids' => ['hy-p:StandingWater'],
			'uri' => 'https://inspire.ec.europa.eu/featureconcept/StandingWater',
			'title' => 'Standing Water',
			'title_de' => 'Stehendes Gewässer',
			'definition' => 'A body of water that is entirely surrounded by land.',
		),
		(object) array(
			'ids' => ['hy-p:Embankment'],
			'uri' => 'https://inspire.ec.europa.eu/featureconcept/Embankment',
			'title' => 'Embankment',
			'title_de' => 'Böschung',
			'definition' => 'A man-made raised long mound of earth or other material.',
		),
		(object) array(
			'ids' => ['GN.GeographicalNames'],
			'uri' => 'https://inspire.ec.europa.eu/layer/GN.GeographicalNames',
			'title' => 'Geographical names',
			'title_de' => 'Geografische Bezeichnungen',
			'definition' => '',
			'spatialobjecttype' => 'NamedPlace',
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
							$exists = array_column($assets, null, 'id')[$entry->uri] ?? false;
							if (false === $exists) {
								$assets[] = (object) array(
									'id' => $entry->uri,
									'title' => (object) ['de' => $entry->title_de, 'en' => $entry->title],
								);
							}
						}

						foreach($entry->ids as $id) {
							if (str_contains($asset->title, $id)) {
								$exists = array_column($assets, null, 'id')[$entry->uri] ?? false;
								if (false === $exists) {
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
		}

		$ret = (object) array(
			'error' => $error,
			'assets' => $assets,
		);

		return $ret;
	}
?>