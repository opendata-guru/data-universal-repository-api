<?php
	function getAppRuntimeParameter($url) {
		// https://docs.conterra.de/de/mapapps/latest/developersguide/explanation/apprt/apprt.html
		$suffix = '';

		$uri = $url . $suffix;
		$html = get_contents_30sec($uri);

		$start = stripos($html, '$apprt.startApp');
		$start = stripos($html, '{', $start);
		$end = stripos($html, '}', $start) + 1;
		$length = $end - $start;
		$html = trim(substr($html, $start, $length));

		$html = preg_replace('/(\w+):/i', '"\1":', $html);

		return json_decode($html);
	}

	function getAppData($url, $param, $app) {
		$suffix = '/js/apps/' . $app . '/' . $param . '.json?lang=de&app=' . $app;
		$uri = $url . $suffix;
		$html = get_contents_30sec($uri);

		return json_decode($html);
	}

	function getBundleData($url, $param, $app) {
		$suffix = 'resources/jsregistry/root/bundles.json';
		$uri = $url . $suffix;
		$html = get_contents_30sec($uri);

		return json_decode($html);
	}

	function systems($url) {
		$json = getAppRuntimeParameter($url);

		$param = $json->param; // default: 'app'
		$app = $json->defaultApp;

		$data = getAppData($url, $param, $app);
//		$bundle = getBundleData($url, $param, $app);

//		$data->bundles->sf_routing->RoutingModel->routes[x]->name // 'front-page','results-page',...
//		$data->bundles->{'sfsdi_data-export'}->ExportService->url

		$version = $data->bundles->{'sf_product-info'}->ProductInfoProvider->productName;
		$href = $url;

		echo json_encode((object) array(
			'extensions' => $data->load->allowedBundles,
			'system' => 'conterra',
			'url' => $href,
			'version' => $version,
		));
	}
?>