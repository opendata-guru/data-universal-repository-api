<?php
	$loadedSObjects = [];
	$fileSObjects = __DIR__ . '/../../api-data/suppliers.csv';
	$allowedValuesOfParameterType = array(
		'root',
		'country',
		'federal', 'federalPortal', 'federalAgency', 'federalCooperation',
		'state', 'stateAgency',
		'governmentRegion',
		'regionalNetwork', 'regionalPortal',
		'district', 'districtPortal', 'districtAgency',
		'collectiveMunicipality',
		'municipality', 'municipalityPortal', 'municipalityAgency',
		'municipality+state',
	);

	loadMappingFileSObjects($loadedSObjects);
	$hashSObjects = md5(serialize($loadedSObjects));

	function get_contents_sparql($url){
		$headers = [
			'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
			'Accept: application/sparql-results+json',
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$data = curl_exec($ch);

		curl_close($ch);

		return $data;
	}

	function getWikiQuery($qID) {
        return 'SELECT ' .
            '?item ' .
            '(SAMPLE(?labelDE) as ?labelDE) ' .
            '(SAMPLE(?labelEN) as ?labelEN) ' .
            '(SAMPLE(?germanRegionalKey) as ?germanRegionalKey) ' .
            '' .
            'WHERE {' .
            '  BIND(wd:' . $qID . ' as ?item)' .
            '' .
            '  OPTIONAL {' .
			'    ?item rdfs:label ?labelDE .' .
			'    filter(lang(?labelDE) = "de" )' .
			'  }' .
            '  BIND(IF( BOUND( ?labelDE), ?labelDE, "") AS ?labelDE)' .
            '' .
            '  OPTIONAL {' .
			'    ?item rdfs:label ?labelEN .' .
			'    filter(lang(?labelEN) = "en" )' .
			'  }' .
            '  BIND(IF( BOUND( ?labelEN), ?labelEN, "") AS ?labelEN)' .
            '' .
            '  OPTIONAL { ?item wdt:P1388 ?germanRegionalKey. }' .
            '  BIND(IF( BOUND( ?germanRegionalKey), ?germanRegionalKey, "") AS ?germanRegionalKey)' .
            '}' .
            'GROUP BY ?item';
	}

	function getSObject() {
		global $loadedSObjects;

		$parameterSID = htmlspecialchars($_GET['sid']);
		$error = null;
		$sObject = null;

		if ($parameterSID == '') {
			$error = (object) array(
				'error' => 400,
				'header' => 'HTTP/1.0 400 Bad Request',
				'message' => 'Bad Request. Path parameter for \'sID\' is not set',
				'parameter' => $parameterSID,
			);
		}

		if (!$error) {
			foreach($loadedSObjects as $object) {
				if ($object->sid == $parameterSID) {
					$sObject = $object;
				}
			}

			if (is_null($sObject)) {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Unknown ID in the \'sID\' path parameter.',
					'parameter' => $parameterSID,
				);
			}
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterSID,
			'sObject' => $sObject,
		);
	}

	function postSObject() {
		global $allowedValuesOfParameterType;

		$parameterType = trim(htmlspecialchars($_GET['type']));
		$parameterSameAsWikidata = trim(htmlspecialchars($_GET['sameaswikidata']));
		$parameterPartOfWikidata = trim(htmlspecialchars($_GET['partofwikidata']));

		if (($parameterSameAsWikidata == '') && ($parameterPartOfWikidata == '')) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Parameter \'sameaswikidata\' and \'partofwikidata\' are not set',
			));
			exit;
		}
		if (!in_array($parameterType, $allowedValuesOfParameterType)) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Value of parameter \'type\' not allowed',
			));
			exit;
		}

		$basePath = 'https://query.wikidata.org/sparql';
		$qIDsameAs = '';
		$qIDpartOf = '';
		$valuesSameAs = null;
		$valuesPartOf = null;

		$sObject = findSObjectByWikidata($parameterSameAsWikidata, $parameterPartOfWikidata);
		if (!$sObject) {
			if ($parameterSameAsWikidata != '') {
				$qIDsameAs = end(explode('/', $parameterSameAsWikidata));
				$url = $basePath . '?query=' . rawurlencode(getWikiQuery($qIDsameAs));
				$data = get_contents_sparql($url);
				$valuesSameAs = json_decode($data)->results->bindings[0];
			}

			if ($parameterPartOfWikidata != '') {
				$qIDpartOf = end(explode('/', $parameterPartOfWikidata));
				$url = $basePath . '?query=' . rawurlencode(getWikiQuery($qIDpartOf));
				$data = get_contents_sparql($url);
				$valuesPartOf = json_decode($data)->results->bindings[0];
			}

			$labelDE = !is_null($valuesSameAs) ? $valuesSameAs->labelDE->value : $valuesPartOf->labelDE->value;
			$labelEN = !is_null($valuesSameAs) ? $valuesSameAs->labelEN->value : $valuesPartOf->labelEN->value;
			$valuesSameAs_ = !is_null($valuesSameAs) ? $valuesSameAs->item->value : '';
			$valuesPartOf_ = !is_null($valuesPartOf) ? $valuesPartOf->item->value : '';
			$germanRegionalKey = !is_null($valuesSameAs) ? $valuesSameAs->germanRegionalKey->value : $valuesPartOf->germanRegionalKey->value;

			$sObject = pushSObject($labelDE, $labelEN, $parameterType, $valuesSameAs_, $valuesPartOf_, $germanRegionalKey);
		}
		saveMappingFileSObjects();

		return $sObject;
	}

	function loadMappingFileSObjects(&$mapping) {
		global $fileSObjects;

		$idGermanRegionalKey = null;
		$idPartOfWikidata = null;
		$idSameAsWikidata = null;
		$idTitleDE = null;
		$idTitleEN = null;
		$idType = null;
		$idSID = null;

		$lines = explode("\n", file_get_contents($fileSObjects));
		$mappingHeader = str_getcsv($lines[0], ',');

		for ($m = 0; $m < count($mappingHeader); ++$m) {
			if ($mappingHeader[$m] === 'sid') {
				$idSID = $m;
			} else if ($mappingHeader[$m] === 'title@EN') {
				$idTitleEN = $m;
			} else if ($mappingHeader[$m] === 'title@DE') {
				$idTitleDE = $m;
			} else if ($mappingHeader[$m] === 'type') {
				$idType = $m;
			} else if ($mappingHeader[$m] === 'sameAsWikidata') {
				$idSameAsWikidata = $m;
			} else if ($mappingHeader[$m] === 'partOfWikidata') {
				$idPartOfWikidata = $m;
			} else if ($mappingHeader[$m] === 'germanRegionalKey') {
				$idGermanRegionalKey = $m;
			}
		}

		array_shift($lines);
		foreach($lines as $line) {
			if ($line != '') {
				$arr = str_getcsv($line, ',');

				$mapping[] = (object) array(
					'sid' => $arr[$idSID] ?: '',
					'title' => array (
						'de' => $arr[$idTitleDE] ?: '',
						'en' => $arr[$idTitleEN] ?: '',
					),
					'type' => $arr[$idType] ?: '',
					'sameAs' => array (
						'wikidata' => $arr[$idSameAsWikidata] ?: '',
					),
					'partOf' => array (
						'wikidata' => $arr[$idPartOfWikidata] ?: '',
					),
					'geocoding' => array (
						'germanRegionalKey' => $arr[$idGermanRegionalKey] ?: '',
					),
				);
			}
		}
	}

	function saveMappingFileSObjects() {
		global $loadedSObjects;
		global $hashSObjects;
		global $fileSObjects;

		$newHash = md5(serialize($loadedSObjects));

		if ($hashSObjects !== $newHash) {
			$header = [
				'sid',
				'title@EN',
				'title@DE',
				'type',
				'sameAsWikidata',
				'partOfWikidata',
				'germanRegionalKey'
			];

			$fp = fopen($fileSObjects, 'wb');
			fputcsv($fp, $header, ',');
			foreach ($loadedSObjects as $sObject) {
				fputcsv($fp, [
					$sObject->sid,
					$sObject->title['en'],
					$sObject->title['de'],
					$sObject->type,
					$sObject->sameAs['wikidata'],
					$sObject->partOf['wikidata'],
					$sObject->geocoding['germanRegionalKey']
				], ',');
			}
			fclose($fp);

			$hashSObjects = $newHash;
		}
	}

	function pushSObject($labelDE, $labelEN, $parameterType, $valuesSameAs, $valuesPartOf, $germanRegionalKey) {
		global $loadedSObjects;

		$loadedSObjects[] = (object) array(
			'sid' => createSID(),
			'title' => array (
				'de' => $labelDE,
				'en' => $labelEN,
			),
			'type' => $parameterType,
			'sameAs' => array (
				'wikidata' => $valuesSameAs,
			),
			'partOf' => array (
				'wikidata' => $valuesPartOf,
			),
			'geocoding' => array (
				'germanRegionalKey' => $germanRegionalKey,
			),
		);

		return end($loadedSObjects);
	}

	function createSID() {
		global $loadedSObjects;

		// https://www.rechner.club/kombinatorik/anzahl-variationen-geordnet-ohne-wiederholung-berechnen
		// objects  | 61    | 61      | 61         | 61
		// draws    | 2     | 3       | 4          | 5
		// variants | 3,660 | 215,940 | 12,524,520 | 713,897,640

		$ALLOWED_CHARS = '0123456789abcdefghijklmnopqrtuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$prefix = 's';
		$length = 4;

		$usedSIDs = [];
		foreach($loadedSObjects as $sObject) {
			$usedSIDs[] = $sObject->sid;
		}
		$usedSIDs = array_filter($usedSIDs);

		do {
			$sid = $prefix . substr(str_shuffle($ALLOWED_CHARS), 0, $length);
		} while(in_array($sid, $usedSIDs));

		return $sid;
	}

/*	function findSObject($sid, $identifier) {
		global $loadedSObjects;

		foreach($loadedSObjects as $sObject) {
			if (($sid === $sObject->sid) && ($identifier === $sObject->identifier)) {
				return $sObject;
			}
		}

		return null;
	}*/

	function findSObjectByWikidata($sameAsWikidata, $partOfWikidata) {
		global $loadedSObjects;

		if (($sameAsWikidata == '') && ($partOfWikidata == '')) {
			return null;
		}

		$qIDsameAs = end(explode('/', $sameAsWikidata));
		$qIDpartOf = end(explode('/', $partOfWikidata));

		foreach($loadedSObjects as $sObject) {
			if ($sameAsWikidata != '') {
				if ($qIDsameAs == end(explode('/', $sObject->sameAs['wikidata']))) {
					return $sObject;
				}
			} else {
				if ($qIDpartOf == end(explode('/', $sObject->partOf['wikidata']))) {
					return $sObject;
				}
			}
		}

		return null;
	}

/*	function updateSObject(&$obj) {
		global $loadedSObjects;

		foreach($loadedSObjects as &$sObject) {
			if (($obj['sid'] === $sObject->sid) && ($obj['identifier'] === $sObject->identifier)) {
				$sObject->lastseen = date('Y-m-d');
				return;
			}
		}

		$obj['lastseen'] = date('Y-m-d');

		$loadedSObjects[] = $obj;
	}*/
?>