<?php
	$mappingTitle = 0;
	$mappingContributor = 1;
	$mappingType = 2;
	$mappingRS = 3;
	$mappingAssociatedRS = 4;
	$mappingWikidata = 5;
	$mappingLink = 6;
	$mappingURI1 = 7;
	$mappingURI2 = 8;
	$mappingURI3 = 9;
	$mappingURI4 = 10;
	$mapping = [];

	loadMappingFile('../api-data/portals.at.csv', $mapping);
	loadMappingFile('../api-data/portals.ch.csv', $mapping);
	loadMappingFile('../api-data/portals.de.csv', $mapping);
	loadMappingFile('../api-data/portals.eu.csv', $mapping);

	function loadMappingFile($file, &$mapping) {
		$idRS = null;
		$idURI1 = null;
		$idURI2 = null;
		$idURI3 = null;
		$idURI4 = null;
		$idLink = null;
		$idType = null;
		$idTitle = null;
		$idWikidata = null;
		$idContributor = null;
		$idAssociatedRS = null;

		$lines = explode("\n", file_get_contents($file));
		$mappingHeader = str_getcsv($lines[0], ',');

		for ($m = 0; $m < count($mappingHeader); ++$m) {
			if ($mappingHeader[$m] === 'parent_and_id_1') {
				$idURI1 = $m;
			} else if ($mappingHeader[$m] === 'parent_and_id_2') {
				$idURI2 = $m;
			} else if ($mappingHeader[$m] === 'parent_and_id_3') {
				$idURI3 = $m;
			} else if ($mappingHeader[$m] === 'parent_and_id_4') {
				$idURI4 = $m;
			} else if ($mappingHeader[$m] === 'title') {
				$idTitle = $m;
			} else if ($mappingHeader[$m] === 'url') {
				$idContributor = $m;
			} else if ($mappingHeader[$m] === 'type') {
				$idType = $m;
			} else if ($mappingHeader[$m] === 'rs') {
				$idRS = $m;
			} else if ($mappingHeader[$m] === 'associated_rs') {
				$idAssociatedRS = $m;
			} else if ($mappingHeader[$m] === 'wikidata') {
				$idWikidata = $m;
			} else if ($mappingHeader[$m] === 'api_list_children') {
				$idLink = $m;
			}
		}

		array_shift($lines);
		foreach($lines as $line) {
			if ($line != '') {
				$arr = str_getcsv($line, ',');
				$mapping[] = [
					$arr[$idTitle] ?: '',
					$arr[$idContributor] ?: '',
					$arr[$idType] ?: '',
					$arr[$idRS] ?: '',
					$arr[$idAssociatedRS] ?: '',
					$arr[$idWikidata] ?: '',
					$arr[$idLink] ?: '',
					$arr[$idURI1] ?: '',
					$arr[$idURI2] ?: '',
					$arr[$idURI3] ?: '',
					$arr[$idURI4] ?: ''
				];
			}
		}
	}

	function semanticContributor($uriDomain, $obj) {
		global $mapping, $mappingURI1, $mappingURI2, $mappingURI3, $mappingURI4, $mappingLink, $mappingType, $mappingTitle, $mappingRS, $mappingAssociatedRS, $mappingWikidata, $mappingContributor;

		$obj['contributor'] = '';
		$obj['type'] = '';
		$obj['wikidata'] = '';
		$obj['link'] = '';

		foreach($mapping as $line) {
			if (   (($line[$mappingURI1] !== '') && ($line[$mappingURI1] == $obj['uri']))
				|| (($line[$mappingURI2] !== '') && ($line[$mappingURI2] == $obj['uri']))
				|| ($line[$mappingURI3] && ($line[$mappingURI3] !== '') && ($line[$mappingURI3] == $obj['uri']))
				|| ($line[$mappingURI4] && ($line[$mappingURI4] !== '') && ($line[$mappingURI4] == $obj['uri']))
			) {
				$obj['title'] = $line[$mappingTitle];
				$obj['contributor'] = $line[$mappingContributor];
				$obj['type'] = $line[$mappingType];
				$obj['rs'] = $line[$mappingRS];
				$obj['associated_rs'] = $line[$mappingAssociatedRS];
				$obj['wikidata'] = $line[$mappingWikidata];
				$obj['link'] = $line[$mappingLink];
			} else if (
				   (($line[$mappingURI1] !== '') && ($line[$mappingURI1] == ($uriDomain . '|' . $obj['name'])))
				|| (($line[$mappingURI2] !== '') && ($line[$mappingURI2] == ($uriDomain . '|' . $obj['name'])))
				|| ($line[$mappingURI3] && ($line[$mappingURI3] !== '') && ($line[$mappingURI3] == ($uriDomain . '|' . $obj['name'])))
				|| ($line[$mappingURI4] && ($line[$mappingURI4] !== '') && ($line[$mappingURI4] == ($uriDomain . '|' . $obj['name'])))
			) {
				$obj['title'] = $line[$mappingTitle];
				$obj['contributor'] = $line[$mappingContributor];
				$obj['type'] = $line[$mappingType];
				$obj['rs'] = $line[$mappingRS];
				$obj['associated_rs'] = $line[$mappingAssociatedRS];
				$obj['wikidata'] = $line[$mappingWikidata];
				$obj['link'] = $line[$mappingLink];
			}
		}

		unset($obj['uri']);

		return $obj;
	}
?>