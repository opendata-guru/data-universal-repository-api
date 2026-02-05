<?php
	function systemPiveau($url) {
		$PIVEAU_HOST_SEARCH_PIVEAU = 'search.piveau.';
		$PIVEAU_HOST_REPO_PIVEAU = 'repo.piveau.';
		$PIVEAU_HOST_UI_PIVEAU = 'ui.piveau.';

		$searchYAML = '/openapi.yaml';
		$searchSuffix = '/api/hub/search' . $searchYAML;
		$repoYAML = '/openapi.yaml';
		$repoSuffix = '/api/hub/repo' . $repoYAML;
		$mqaSuffix = '/api/mqa/cache/openapi.yaml';
		$mqaSHACLSuffix = '/api/mqa/shacl/openapi-shacl.yaml';
		// $sparqlSuffix = '/sparql';
		// $useCasesSuffix = '/en/export-use-cases';
		$host = '';

		function getVersion($path) {
//			$yaml = file_get_contents($path);
			$yaml = get_contents($path);

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

		$link = parse_url($url);
		if (str_starts_with($link['host'], $PIVEAU_HOST_SEARCH_PIVEAU)) {
    		$host = substr($link['host'], strlen($PIVEAU_HOST_SEARCH_PIVEAU));
		} else if (str_starts_with($link['host'], $PIVEAU_HOST_UI_PIVEAU)) {
    		$host = substr($link['host'], strlen($PIVEAU_HOST_UI_PIVEAU));
		}

		if ($host) {
			$link['host'] = $PIVEAU_HOST_SEARCH_PIVEAU . $host;
			$url = unparse_url($link);
			$versionSearch = getVersion($url . $searchYAML);

			$link['host'] = $PIVEAU_HOST_REPO_PIVEAU . $host;
			$url = unparse_url($link);
			$versionRegistry = getVersion($url . $repoYAML);

			$versionMQA = '';
			$versionSHACLMetadataValidation = '';
		} else {
			$versionSearch = getVersion($url . $searchSuffix);
			$versionRegistry = getVersion($url . $repoSuffix);
			$versionMQA = getVersion($url . $mqaSuffix);
			$versionSHACLMetadataValidation = getVersion($url . $mqaSHACLSuffix);
		}

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