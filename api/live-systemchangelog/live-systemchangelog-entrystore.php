<?php
	function liveSystemChangelogEntryStore() {
		$CHANGELOG_DEV = 'https://bitbucket.org/metasolutions/entryscape/raw/c751fafacd16d8563f4ab589331e9135a673c8c8/CHANGELOG.md';
		$CHANGELOG = 'https://bitbucket.org/metasolutions/entryscape/raw/e716c42574d2d6003db40a445127c6f3f5d59cfa/CHANGELOG.md';

		$md = get_contents($CHANGELOG);
		$content = preg_split("/\r\n|\n|\r/", $md);
		$list = [];

		foreach ($content as $index=>$line) {
			if (substr($line, 0, 3) === '## ') {
				// ## [3.17.12](https://.../compare/3.17.12%0D3.17.11) - 2026-03-03

				$parts = explode(' ', $line);

				try {
					$date = new DateTime($parts[3]);
				} catch (Exception $e) {
					continue;
				}
				$version = explode(']', trim($parts[1], '['))[0];

				$list[] = (object) array(
					'date' => $date->format('Y-m-d'),
					'version' => $version,
				);
			}
		}

		echo json_encode((object) array(
			'entrystore' => addColors($list),
		));
	}
?>