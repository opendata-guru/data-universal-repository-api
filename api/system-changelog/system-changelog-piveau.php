<?php
	function systemChangelogPiveau() {
		// unknown repos
		$CHANGELOG = 'https://gitlab.com/piveau/hub/piveau-hub-statistics/-/raw/master/CHANGELOG.md?ref_type=heads';
		$CHANGELOG = 'https://gitlab.com/piveau/hub/piveau-hub-store/-/raw/master/CHANGELOG.md?ref_type=heads';
		$CHANGELOG = 'https://gitlab.com/piveau/hub/piveau-hub-translation/-/raw/master/CHANGELOG.md?ref_type=heads';

		// Search Version
		$CHANGELOG = 'https://gitlab.com/piveau/hub/piveau-hub-search/-/raw/master/CHANGELOG.md?ref_type=heads';
		// SHACL Validator Version
		$CHANGELOG = 'https://gitlab.com/piveau/metrics/piveau-metrics-validating-shacl/-/raw/develop/CHANGELOG.md?ref_type=heads';
		// Registry Version
		$CHANGELOG = 'https://gitlab.com/piveau/hub/piveau-hub-repo/-/raw/master/CHANGELOG.md?ref_type=heads';

		$md = get_contents($CHANGELOG);
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

		echo json_encode((object) array(
			'history' => addColors($list),
		));
	}
?>