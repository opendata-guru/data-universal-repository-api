<?php
	function systemPiveau($url) {
		$searchSuffix = '/api/hub/search/openapi.yaml';
		$repoSuffix = '/api/hub/repo/openapi.yaml';
		$mqaSuffix = '/api/mqa/cache/openapi.yaml';
		$mqaSHACLSuffix = '/api/mqa/shacl/openapi-shacl.yaml';
		// $sparqlSuffix = '/sparql';
		// $useCasesSuffix = '/en/export-use-cases';

		function getVersion($path) {
			$yaml = file_get_contents($path);

			if ($yaml) {
				$lines = preg_split("/\r\n|\n|\r/", $yaml);
				$version = '';
				$l = 0;
				for (; $l < count($lines); ++$l) {
					$line = $lines[$l];
					if ($line === 'info:') {
						break;
					}
				}
				for (; $l < count($lines); ++$l) {
					$line = $lines[$l];
					if (0 === strpos($line, '  version:')) {
						return trim(str_replace('version:', '', $line));
					}
				}
			}

			return '';
		}

		$versionSearch = getVersion($url . $searchSuffix);
		$versionRegistry = getVersion($url . $repoSuffix);
		$versionMQA = getVersion($url . $mqaSuffix);
		$versionSHACLMetadataValidation = getVersion($url . $mqaSHACLSuffix);

		if ($version !== '') {
			echo json_encode((object) array(
				'extensions' => array(
					'MQA' => $versionMQA,
					'registry' => $versionRegistry,
					'search' => $versionSearch,
					'SHACL metadata validation' => $versionSHACLMetadataValidation,
				),
				'system' => 'Piveau',
				'url' => $url,
				'version' => $versionSearch,
			));
		} else {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. The underlying system (Piveau) could not be detected',
			));
		}
	}
?>