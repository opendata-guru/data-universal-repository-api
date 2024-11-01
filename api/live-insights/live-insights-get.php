<?php
	if (file_exists('live-insights/live-insights-curl.php')) {
		include('live-insights/live-insights-curl.php');
		include('live-insights/live-insights-interpreter.php');
		include('live-insights/live-insights-parser.php');
	} else {
		include('../live-insights/live-insights-curl.php');
		include('../live-insights/live-insights-interpreter.php');
		include('../live-insights/live-insights-parser.php');
	}

	// wrong
	// https://geo.sv.rostock.de/inspire/plu-localplans/download
	// https://geo.sv.rostock.de/inspire/plu-localplans/view
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/download
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/view
	// right
	// https://geo.sv.rostock.de/inspire/plu-localplans/download?service=WFS&version=2.0.0&request=GetCapabilities
	// https://geo.sv.rostock.de/inspire/plu-localplans/view?service=WMS&version=1.3.0&request=GetCapabilities
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/download?service=WFS&version=2.0.0&request=GetCapabilities
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/view?service=WMS&version=1.3.0&request=GetCapabilities

	function getInsights($url) {
		$ret = (object) array(
			'passes' => [],
		);

		$content = curl($url);
		$parsed = parser($content);
		unset($content->content);
		$interpreted = interpret($content, $parsed);

		$pass = (object) array(
			'file' => $content,
			'content' => $parsed,
			'interpreter' => $interpreted,
		);
		$ret->passes[] = $pass;

		return $ret;
	}
?>