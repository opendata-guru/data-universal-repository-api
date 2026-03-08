<?php
	function parseChangeLog($url) {
		$md = get_contents($url);
		$content = preg_split("/\r\n|\n|\r/", $md);
		$list = [];

		foreach ($content as $line) {
			if (substr($line, 0, 3) === '## ') {
				$parts = explode(' ', $line);
				$date = trim($parts[2], '()');
				$version = trim($parts[1], ' ');

				if ($version !== 'Unreleased') {
					$list[] = (object) array(
						'date' => $date,
						'version' => $version,
					);
				}
			}
		}

		return addColors($list);
	}

	function liveSystemChangelogPiveau() {
		// unknown repos
		$CHANGELOG = 'https://gitlab.com/piveau/hub/piveau-hub-statistics/-/raw/master/CHANGELOG.md?ref_type=heads';
		$CHANGELOG = 'https://gitlab.com/piveau/hub/piveau-hub-statistics-ui/-/raw/master/CHANGELOG.md?ref_type=heads';
		$CHANGELOG = 'https://gitlab.com/piveau/hub/piveau-hub-translation/-/raw/master/CHANGELOG.md?ref_type=heads';
		$CHANGELOG = 'https://gitlab.com/piveau/hub/piveau-hub-ui/-/raw/master/CHANGELOG.md?ref_type=heads';

		$search = 'https://gitlab.com/piveau/hub/piveau-hub-search/-/raw/master/CHANGELOG.md?ref_type=heads';
		$store = 'https://gitlab.com/piveau/hub/piveau-hub-store/-/raw/master/CHANGELOG.md?ref_type=heads';
		$shaclValidator = 'https://gitlab.com/piveau/metrics/piveau-metrics-validating-shacl/-/raw/develop/CHANGELOG.md?ref_type=heads';
		$metricsCache = 'https://gitlab.com/piveau/metrics/piveau-metrics-cache/-/raw/master/CHANGELOG.md?ref_type=heads';
		$registryRepo = 'https://gitlab.com/piveau/hub/piveau-hub-repo/-/raw/master/CHANGELOG.md?ref_type=heads';

		echo json_encode((object) array(
			'metrics' => parseChangeLog($metricsCache),
			'registry' => parseChangeLog($registryRepo),
			'search' => parseChangeLog($search),
			'shacl' => parseChangeLog($shaclValidator),
			'store' => parseChangeLog($store),
		));
	}
?>