<?php
	// https://github.com/cubiclesoft/js-fileexplorer

	$dict = array(
		'de' => (object) array(
			'all_objects' => 'Alle Objekte',
			'new_objects' => 'Neue Objekte',
			'defects' => 'Defekte Objekte',
			'member_states' => 'EU-Mitgliedstaaten',
			'attic' => 'Dachboden',
		),
		'en' => (object) array(
			'all_objects' => 'All objects',
			'new_objects' => 'New objects',
			'defects' => 'Defective objects',
			'member_states' => 'EU member states',
			'attic' => 'Attic',
		),
	);

	function getEntryHash($path, $file)
	{
		return md5($path . '|' . $file);
	}

	function getTooltip($title, $arr) {
		$tooltip = array();

		if (strlen($title) > 35) {
			$tooltip[] = $title;
		}

		if ($arr) {
			$tooltip = array_merge($tooltip, $arr);
		}

		return implode("\n", $tooltip);
	}

	function buildFile($path, $file) {
		$entry = array(
			'id' => $file->id,
			'name' => $file->title,
			'type' => 'file',
			'hash' => getEntryHash($path, $file->id),

			// optional
			'tooltip' => getTooltip($file->title, [$file->error]),
//			'size' => 127,
		);

//		$entry["thumb"] = $options["base_url"] . substr($path, strlen($options["base_dir"])) . "/" . $file;
//		$entry["thumb"] = $options["thumbs_url"] . $filename;
//		$entry["thumb"] = $options["thumb_create_url"] . (strpos($options["thumb_create_url"], "?") !== false ? "&" : "?") . "path=" . urlencode(json_encode(explode("/", substr($path, strlen($options["base_dir"]))), JSON_UNESCAPED_SLASHES)) . "&id=" . urlencode($file);
//		$entry["thumb"] = $options["base_url"] . substr($path, strlen($options["base_dir"])) . "/" . $file;
//		$entry["thumb"] = $options["thumbs_url"] . $filename;

		return $entry;
	}

	function buildFolder($path, $folder) {
		$entry = array(
			'id' => $folder->id,
			'name' => $folder->title,
			'type' => 'folder',
			'hash' => getEntryHash($path, $folder->id),

			// optional
			'tooltip' => getTooltip($folder->title, []),
//			'overlay' => 'overlay_own_class',
//			'attrs' => ???,
//			'thumb' => 'https://opendata.guru/govdata/assets/folder.svg',
		);

		return $entry;
	}

	function getSampleFile($path, $lang, $result) {
		$result->files[] = (object) array(
			'id' => 'foobar',
			'title' => 'File Name.txt',
			'error' => null,
		);

		return $result;
	}

	function getError($iObject) {
		if (!$iObject) {
			return null;
		}

		$error = isset($iObject->insights) ? $iObject->insights->error : null;

		if ($error) {
			if ('string' === gettype($error)) {
				return trim($error);
			} else {
				if (isset($error->{'@attributes'}) && isset($error->{'@attributes'}->code)) {
					return $error->{'@attributes'}->code;
				} else {
					return json_encode($error);
				}
			}
		}

		return null;
	}

	function getFilesAndFoldersHVDDefects($path, $lang, $result) {
		global $dict;

		$iObjects = getErrorIObject();

		if (count($path) < 1) {
			$folders = [];

			foreach($iObjects as $iObject) {
				$error = getError($iObject);

				$folders[$error] = $error;
			}

			foreach($folders as $folder) {
				$result->folders[] = (object) array(
					'id' => $folder,
					'title' => $folder
				);
			}
		} else {
			$level = $path[0];
			array_shift($path);

			foreach($iObjects as $iObject) {
				if($level === getError($iObject)) {
					$name = $iObject->url;
					$name = trim($name, '/');
					$name = end(explode('/', $name));
					$name = reset(explode('?', $name));

					$result->files[] = (object) array(
						'id' => $iObject->iid,
						'title' => $name,
						'error' => $error,
					);
				}
			}
		}

		return $result;
	}

	function getFilesAndFoldersHVD($path, $lang, $result) {
		global $dict;

		if (count($path) < 1) {
/*			$result->folders[] = (object) array(
				'id' => 'all_objects',
				'title' => $dict[$lang]->all_objects
			);*/
/*			$result->folders[] = (object) array(
				'id' => 'new_objects',
				'title' => $dict[$lang]->new_objects
			);*/
			$result->folders[] = (object) array(
				'id' => 'defects',
				'title' => $dict[$lang]->defects
			);
/*			$result->folders[] = (object) array(
				'id' => 'member_states',
				'title' => $dict[$lang]->member_states
			);*/
/*			$result->folders[] = (object) array(
				'id' => 'attic',
				'title' => $dict[$lang]->attic
			);*/

			return $result;
		}

		$level = $path[0];
		array_shift($path);

		if ('defects' === $level) {
			return getFilesAndFoldersHVDDefects($path, $lang, $result);
		}

		return getSampleFile($path, $lang, $result);
	}

	function getFilesAndFolders($path, $lang) {
		$result = (object) array(
			'files' => [],
			'folders' => [],
		);
		$path = explode('/', $path);

		if (count($path) < 1) {
			return null;
		}
		if ('hvd' === $path[0]) {
			array_shift($path);
			return getFilesAndFoldersHVD($path, $lang, $result);
		}

		return $result;
	}

	function processRefreshAction($param, $lang) {
		$path = isset($param['path']) ? $param['path'] : '';
		$dir = getFilesAndFolders($path, $lang);

		if (!$dir) {
			return array(
				'success' => false,
				'error' => 'Invalid path specified.',
				'errorcode' => 'invalid_path'
			);
		} else {
			$result = array(
				'success' => true,
				'entries' => array()
			);

			foreach($dir->folders as $folder) {
				$entry = buildFolder($path, $folder);
				if ($entry !== false) {
					$result['entries'][] = $entry;
				}
			}

			foreach($dir->files as $file) {
				$entry = buildFile($path, $file);
				if ($entry !== false) {
					$result['entries'][] = $entry;
				}
			}
		}

		return $result;
	}

	function handleFileExplorer() {
		global $dict;

		$param = $_POST;
		$action = isset($param['action']) ? $param['action'] : '';
		$lang = strtolower(isset($param['lang']) ? substr($param['lang'], 0, 2) : 'en');

		if (!isset($dict[$lang])) {
			$lang = 'en';
		}

		if ('file_explorer_refresh' === $action) {
			return processRefreshAction($param, $lang);
		}
		/*
		file_explorer_thumbnail
		file_explorer_rename
		file_explorer_file_info
		file_explorer_load_file
		file_explorer_save_file
		file_explorer_new_folder
		file_explorer_new_file
		file_explorer_upload_init
		file_explorer_download
		file_explorer_copy_init
		file_explorer_copy
		file_explorer_move
		file_explorer_recycle
		file_explorer_delete
		*/

		return null;
	}
?>