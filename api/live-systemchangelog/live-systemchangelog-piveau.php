<?php
	function getChangeLog($url) {
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

		return $list;
	}

	function parseChangeLog($url) {
		$list = getChangeLog($url);
		return addColors($list);
	}

	function sortList($a, $b) {
		$aMajor = explode('.', $a->version)[0];
		$bMajor = explode('.', $b->version)[0];

		if ($aMajor !== $bMajor) {
			return $aMajor < $bMajor ? 1 : -1;
		}

		$aMinor = explode('.', $a->version)[1];
		$bMinor = explode('.', $b->version)[1];

		if ($aMinor !== $bMinor) {
			return $aMinor < $bMinor ? 1 : -1;
		}

		$aPatch = explode('.', $a->version)[2];
		$bPatch = explode('.', $b->version)[2];

		if ($aPatch !== $bPatch) {
			return $aPatch < $bPatch ? 1 : -1;
		}

		return 0;
	}

	function parseTwoChangeLogs($url1, $url2) {
		$list1 = getChangeLog($url1);
		$list2 = getChangeLog($url2);
		$list = array_merge($list1, $list2);

		usort($list, 'sortList');

		$result = [];
		foreach($list as $l) {
			$found = false;

			foreach($result as $r) {
				if ($l->version === $r->version) {
					$found = true;
				}
			}

			if (!$found) {
				$result[] = $l;
			}
		}

		return addColors($result);
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
		$registryRepoDev = 'https://gitlab.com/piveau/hub/piveau-hub-repo/-/raw/develop/CHANGELOG.md?ref_type=heads';

		echo json_encode((object) array(
			'metrics' => parseChangeLog($metricsCache),
			'registry' => parseTwoChangeLogs($registryRepo, $registryRepoDev),
			'search' => parseChangeLog($search),
			'shacl' => parseChangeLog($shaclValidator),
			'store' => parseChangeLog($store),
		));
	}
?>