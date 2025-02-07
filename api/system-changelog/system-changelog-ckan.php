<?php
	function systemChangelogCKAN() {
		$CHANGELOG = 'https://raw.githubusercontent.com/ckan/ckan/master/CHANGELOG.rst';

		$md = get_contents($CHANGELOG);
		$content = preg_split("/\r\n|\n|\r/", $md);
		$list = [];

		foreach ($content as $index=>$line) {
			if (substr($line, 0, 1) === '=') {
				$title = $content[$index - 1];
				$parts = explode(' ', $title);

				try {
					$date = new DateTime($parts[1]);
				} catch (Exception $e) {
					// may be $title === 'v.2.12.0 (Not yet released)'
					continue;
				}
				$version = trim($parts[0], 'v. ');

				$list[] = (object) array(
					'date' => $date->format('Y-m-d'),
					'version' => $version,
				);
			}
		}

		echo json_encode((object) array(
			'history' => addColors($list),
		));
	}
?>