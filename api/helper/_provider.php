<?php
	$loadedProviders = [];

	loadMappingFileProviders(__DIR__ . '/../../api-data/providers.csv', $loadedProviders);

	function getProvider() {
		global $loadedProviders;

		$parameterPID = htmlspecialchars($_GET['pID']);
		$parameterPID2 = htmlspecialchars($_GET['pid']);
		$error = null;
		$url = '';

		if ($parameterPID == '') {
			$parameterPID = $parameterPID2;
			if ($parameterPID == '') {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Parameter \'pID\' is not set',
					'parameter' => $parameterPID,
				);
			}
		}

		if (!$error) {
			foreach($loadedProviders as $provider) {
				if (providerGetPID($provider) == $parameterPID) {
					$url = providerGetServerURL($provider);
				}
			}

			if ($url === '') {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Unknown ID in the \'pID\' parameter.',
					'parameter' => $parameterPID,
				);
			}
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterPID,
			'url' => $url,
		);
	}

	function loadMappingFileProviders($file, &$mapping) {
		$idServerURL = null;
		$idModified = null;
		$idPID = null;
		$idSID = null;

		$lines = explode("\n", file_get_contents($file));
		$mappingHeader = str_getcsv($lines[0], ',');

		for ($m = 0; $m < count($mappingHeader); ++$m) {
			if ($mappingHeader[$m] === 'pid') {
				$idPID = $m;
			} else if ($mappingHeader[$m] === 'sid') {
				$idSID = $m;
			} else if ($mappingHeader[$m] === 'serverurl') {
				$idServerURL = $m;
			} else if ($mappingHeader[$m] === 'modified') {
				$idModified = $m;
			}
		}

		array_shift($lines);
		foreach($lines as $line) {
			if ($line != '') {
				$arr = str_getcsv($line, ',');
				$mapping[] = [
					$arr[$idPID] ?: '',
					$arr[$idSID] ?: '',
					$arr[$idServerURL] ?: '',
					$arr[$idModified] ?: ''
				];
			}
		}
	}

	function providerGetPID($provider) {
		return $provider[0];
	}
	function providerGetSID($provider) {
		return $provider[1];
	}
	function providerGetServerURL($provider) {
		return $provider[2];
	}

	function findPObjectByLink($link) {
		global $loadedProviders;

		foreach($loadedProviders as $pObject) {
			if (providerGetServerURL($pObject) == $link->url) {
				return $pObject;
			}
			if (providerGetServerURL($pObject) == $link->parameter) {
				return $pObject;
			}
		}

		return null;
	}
?>