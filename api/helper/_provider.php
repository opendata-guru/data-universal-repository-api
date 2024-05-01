<?php
	$loadedProviders = [];

	loadMappingFile(__DIR__ . '/../../api-data/providers.csv', $loadedProviders);

	function loadMappingFile($file, &$mapping) {
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
?>