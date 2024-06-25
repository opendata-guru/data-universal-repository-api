<?php
	$loadedSObjects = [];
	$fileSObjects = __DIR__ . '/../../api-data/suppliers.csv';
	$allowedValuesOfParameterType = array('root','municipality');

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

	function postSObject() {
		global $loadedProviders;
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

		$basePath = 'https://query.wikidata.org/sparql';
		$qIDsameAs = '';
		$qIDpartOf = '';
		$valuesSameAs = null;
		$valuesPartOf = null;

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

		if (!in_array($parameterType, $allowedValuesOfParameterType)) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Value of parameter \'type\' not allowed',
			));
			exit;
		}

		return (object) array(
			'sid' => createSID(),
			'title' => array (
				'de' => !is_null($valuesSameAs) ? $valuesSameAs->labelDE->value : $valuesPartOf->labelDE->value,
				'en' => !is_null($valuesSameAs) ? $valuesSameAs->labelEN->value : $valuesPartOf->labelEN->value,
			),
			'type' => $parameterType,
			'sameAs' => array (
				'wikidata' => !is_null($valuesSameAs) ? $valuesSameAs->item->value : '',
			),
			'partOf' => array (
				'wikidata' => !is_null($valuesPartOf) ? $valuesPartOf->item->value : '',
			),
			'geocoding' => array (
				'germanRegionalKey' => !is_null($valuesSameAs) ? $valuesSameAs->germanRegionalKey->value : $valuesPartOf->germanRegionalKey->value,
			),
		);
	}

	function loadMappingFileSObjects(&$mapping) {
		global $fileSObjects;

		$idPartOfWikidata = null;
		$idSameAsWikidata = null;
		$idPartOfRS = null;
		$idSameAsRS = null;
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
			} else if ($mappingHeader[$m] === 'sameAsRS') {
				$idSameAsRS = $m;
			} else if ($mappingHeader[$m] === 'partOfWikidata') {
				$idPartOfWikidata = $m;
			} else if ($mappingHeader[$m] === 'partOfRS') {
				$idPartOfRS = $m;
			}
		}

		array_shift($lines);
		foreach($lines as $line) {
			if ($line != '') {
				$arr = str_getcsv($line, ',');

				$sObject = [];
				$sObject['sid'] = $arr[$idSID] ?: '';
				$sObject['title@EN'] = $arr[$idTitleEN] ?: '';
				$sObject['title@DE'] = $arr[$idTitleDE] ?: '';
				$sObject['type'] = $arr[$idType] ?: '';
				$sObject['sameAsWikidata'] = $arr[$idSameAsWikidata] ?: '';
				$sObject['sameAsRS'] = $arr[$idSameAsRS] ?: '';
				$sObject['partOfWikidata'] = $arr[$idPartOfWikidata] ?: '';
				$sObject['partOfRS'] = $arr[$idPartOfRS] ?: '';

				$mapping[] = $sObject;
			}
		}
	}

/*	function saveMappingFileSObjects() {
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
				'sameAsRS',
				'partOfWikidata',
				'partOfRS'
			];

			$fp = fopen($fileSObjects, 'wb');
			fputcsv($fp, $header, ',');
			foreach ($loadedSObjects as $line) {
				fputcsv($fp, [
					$line['sid'],
					$line['title@EN'],
					$line['title@DE'],
					$line['type'],
					$line['sameAsWikidata'],
					$line['sameAsRS'],
					$line['partOfWikidata'],
					$line['partOfRS']
				], ',');
			}
			fclose($fp);

			$hashSObjects = $newHash;
		}
	}*/

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
			$usedSIDs[] = $sObject['sid'];
		}
		$usedSIDs = array_filter($usedSIDs);

		do {
			$sid = $prefix . substr(str_shuffle($ALLOWED_CHARS), 0, $length);
		} while(in_array($sid, $usedSIDs));

		return $sid;
	}

/*	function findSObject($pid, $identifier) {
		global $loadedSObjects;

		foreach($loadedSObjects as $sObject) {
			if (($pid === $sObject['pid']) && ($identifier === $sObject['identifier'])) {
				return $sObject;
			}
		}

		return null;
	}*/

/*	function updateSObject(&$obj) {
		global $loadedSObjects;

		foreach($loadedSObjects as &$sObject) {
			if (($obj['pid'] === $sObject['pid']) && ($obj['identifier'] === $sObject['identifier'])) {
				$sObject['lastseen'] = date('Y-m-d');
				return;
			}
		}

		$obj['lastseen'] = date('Y-m-d');

		$loadedSObjects[] = $obj;
	}*/
?>