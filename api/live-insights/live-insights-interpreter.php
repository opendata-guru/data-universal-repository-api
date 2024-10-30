<?php
	// test URLs:
	// https://www.inspire.niedersachsen.de/doorman/noauth/alkis-dls-elu?REQUEST=GetCapabilities&SERVICE=WFS

	$semRegistry = array(
		(object) array(
			'ids' => ['AD.Address', 'ad:Address'],
			'uri' => 'https://inspire.ec.europa.eu/layer/AD.Address',
			'title' => 'Addresses',
			'title_de' => 'Adressen',
			'definition' => '',
			'spatialobjecttype' => 'Address',
		),
		(object) array(
			'ids' => ['ad:AdminUnitName'],
			'uri' => 'https://inspire.ec.europa.eu/schemas/ad/4.0/Addresses.xsd#AdminUnitName',
			'title' => 'Name of the administrative unit',
			'title_de' => 'Name der Verwaltungseinheit',
			'definition' => 'An address component which represents the name of a unit of administration where a Member State has and/or exercises jurisdictional rights, for local, regional and national governance.',
		),
		(object) array(
			'ids' => ['ad:PostalDescriptor'],
			'uri' => 'https://inspire.ec.europa.eu/schemas/ad/4.0/Addresses.xsd#PostalDescriptor',
			'title' => 'Postal Description',
			'title_de' => 'Postalische Beschreibung',
			'definition' => 'An address component which represents the identification of a subdivision of addresses and postal delivery points in a country, region or city for postal purposes.',
		),
		(object) array(
			'ids' => ['ad:ThoroughfareName'],
			'uri' => 'https://inspire.ec.europa.eu/schemas/ad/4.0/Addresses.xsd#ThoroughfareName',
			'title' => 'Name of the thoroughfare',
			'title_de' => 'Name des Verkehrswegs',
			'definition' => 'An address component which represents the name of a passage or way through from one location to another.',
		),
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
			'uri' => 'https://inspire.ec.europa.eu/schemas/elu/4.0/ExistingLandUse.xsd#ExistingLandUseObject',
			'title' => 'existing land use object',
			'title_de' => 'Existierende Bodennutzung',
			'definition' => 'An existing land use object describes the land use of an area having a homogeneous combination of land use types.',
		),
		(object) array(
			'ids' => ['er-v:RenewableAndWasteResource'],
			'uri' => 'https://inspire.ec.europa.eu/schemas/2024.1/er-v/3.0/EnergyResourcesVector.xsd#RenewableAndWasteResource',
			'title' => 'Renewable and waste resource',
			'title_de' => 'Erneuerbare Energien und Abfallressourcen',
			'definition' => 'A spatial object defining an inferred or observable spatial extent of a resource that can be, or has been used as a source of renewable energy or waste.',
		),
		(object) array(
			'ids' => ['GE.GeologicFault'],
			'uri' => 'https://inspire.ec.europa.eu/layer/GE.GeologicFault',
			'title' => 'Geologic faults',
			'title_de' => 'Geologische Verwerfungen',
			'definition' => 'This layer only applies to MappedFeature spatial objects whose specification property is of type ShearDisplacementStructure.',
		),
		(object) array(
			'ids' => ['GN.GeographicalNames'],
			'uri' => 'https://inspire.ec.europa.eu/layer/GN.GeographicalNames',
			'title' => 'Geographical names',
			'title_de' => 'Geografische Bezeichnungen',
			'definition' => '',
			'spatialobjecttype' => 'NamedPlace',
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
			'ids' => ['tn-ro:RoadLink'],
			'uri' => 'https://inspire.ec.europa.eu/schemas/tn-ro/4.0/RoadTransportNetwork.xsd#RoadLink',
			'title' => 'Road link',
			'title_de' => 'Straßenverbindung',
			'definition' => 'A linear spatial object that describes the geometry and connectivity of a road network between two points in the network. Road links can represent paths, bicycle roads, single carriageways, multiple carriageway roads and even fictitious trajectories across traffic squares.',
		),
	);

	function interpret($file, $content) {
		global $semRegistry;

		$error = null;
		$assets = [];

		if ($content && $content->body) {
			$bodyAssets = $content->body->assets ?
				$content->body->assets :
				(property_exists((object) $content->body, 'assets') ?
					(object) $content->body['assets'] :
					null);
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