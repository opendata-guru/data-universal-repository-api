<?php
	// https://github.com/cubiclesoft/js-fileexplorer

	function getPathDepth($path)
	{
		return substr_count($path, '/');
	}

	function getEntryHash($type, $file)
	{
		return md5($type . "|" . $file);
	}

	function getTooltip($path, $file, $type)
	{
		$tooltip = array();

		if (strlen($file) > 35) {
			$tooltip[] = $file;
		}

		return implode("\n", $tooltip);
	}

	function buildEntry($path, $file, $type, $depth)
	{
//		$info = @stat($path . "/" . $file);
//		if ($info === false)  return false;

		$entry = array(
			'id' => $file,
			'name' => $file,
			'type' => $type,
			'hash' => getEntryHash($type, $file),
			'tooltip' => getTooltip($path, $file, $type)
		);

		if (1 > $depth + 1) {
			$entry['attrs'] = array('canmodify' => false);
		}

		if ($type === 'file') {
			$entry['size'] = 1234567;

//			$entry["thumb"] = $options["base_url"] . substr($path, strlen($options["base_dir"])) . "/" . $file;
//			$entry["thumb"] = $options["thumbs_url"] . $filename;
//			$entry["thumb"] = $options["thumb_create_url"] . (strpos($options["thumb_create_url"], "?") !== false ? "&" : "?") . "path=" . urlencode(json_encode(explode("/", substr($path, strlen($options["base_dir"]))), JSON_UNESCAPED_SLASHES)) . "&id=" . urlencode($file);
//			$entry["thumb"] = $options["base_url"] . substr($path, strlen($options["base_dir"])) . "/" . $file;
//			$entry["thumb"] = $options["thumbs_url"] . $filename;
		}

		return $entry;
	}

	function processRefreshAction($post) {
		$path = isset($post['path']) ? $post['path'] : '';

		if (false) {
			return array(
				'success' => false,
				'error' => 'Operation denied.',
				'errorcode' => 'refresh_not_allowed'
			);
		} else if (false) {
			return array(
				'success' => false,
				'error' => 'Invalid path specified.',
				'errorcode' => 'invalid_path'
			);
		} else {
			$dir = '';

			if (false) {
				return array(
					'success' => false,
					'error' => 'The server was unable to open the path.',
					'errorcode' => 'path_open_failed'
				);
			} else {
				$depth = getPathDepth($path);

				$result = array(
					'success' => true,
					'entries' => array()
				);

				$file = 'Folder Name';

				$entry = buildEntry($path, $file, 'folder', $depth);
				if ($entry !== false) {
					$result['entries'][] = $entry;
				}

				$file = 'File Name';

				$entry = buildEntry($path, $file, 'file', $depth);
				if ($entry !== false) {
					$result['entries'][] = $entry;
				}
			}
		}

		return $result;
/*		return array(
			'success' => false,
			'error' => 'Supplied base directory does not exist.',
			'errorcode' => 'invalid_base_dir'
		);*/
	}

	function handleFileExplorer() {
		$post = $_POST;
		$action = isset($post['action']) ? $post['action'] : '';

		if ('file_explorer_refresh' === $action) {
			return processRefreshAction($post);
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