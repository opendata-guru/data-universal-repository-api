<?php
	function getKeywordsWithID($keywords) {
		$ret = [];

		foreach($keywords as $keyword) {
			$keyword = '' . $keyword;
			if (strpbrk($keyword, '-._')) {
				$ret[] = $keyword;
			}
		}

		return $ret;
	}

	function parseXML_EsriWMS($xml, $prefix, &$body) {
		unset($xml->Filter_Capabilities);

		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_FES_20($xml, $prefix, &$body) {
		unset($xml->Filter_Capabilities);

		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_GML($xml, $prefix, &$body) {
		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_InspireCommon($xml, $prefix, &$body) {
		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_InspireDLS($xml, $prefix, &$body) {
		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_InspireVS($xml, $prefix, &$body) {
		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_OGC($xml, $prefix, &$body) {
		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseWMS_Service(&$body, &$service) {
		if ($service->Title) {
			$body['title'] = '' . $service->Title;
			unset($service->Title);
		}
		if ($service->Abstract) {
			$body['description'] = '' . $service->Abstract;
			unset($service->Abstract);
		}

		$body['keywords'] = getKeywordsWithID($service->KeywordList->Keyword);
		unset($service->KeywordList->Keyword);
		if (!(array)$service->KeywordList) unset($service->KeywordList);

		unset($service->AccessConstraints);
		unset($service->ContactInformation);
		unset($service->Fees);
		unset($service->MaxHeight);
		unset($service->MaxWidth);
		unset($service->Name);

		if (!(array)$service->OnlineResource) unset($service->OnlineResource);
	}

	function parseWMS_Capability_Layer(&$body, &$capability) {
		foreach($capability->Layer as $layer) {
			$visible = false;
			if (((array)$layer)['@attributes']) {
				$visible = '1' === ((array)$layer)['@attributes']['queryable'];
//				unset($layer->@attributes);
			}

			// AuthorityURL/OnlineResource <- the attribute contain a URI!!!!

			unset($layer->BoundingBox);
			unset($layer->CRS);
			unset($layer->DataURL);
			unset($layer->EX_GeographicBoundingBox);
			unset($layer->LatLonBoundingBox);
			unset($layer->MaxScaleDenominator);
			unset($layer->MinScaleDenominator);
			unset($layer->MetadataURL);
			unset($layer->ScaleHint);
			unset($layer->SRS);
			unset($layer->Style);

			$keywords = getKeywordsWithID($layer->KeywordList->Keyword);
			unset($layer->KeywordList->Keyword);
			if (!(array)$layer->KeywordList) unset($layer->KeywordList);

			$body['assets'][] = (object) array(
				'name' => '' . $layer->Name,
				'title' => '' . $layer->Title,
				'descriptions' => '' . $layer->Abstract,
				'keywords' => $keywords,
				'identifier' => '' . $layer->Identifier,
				'visible' => $visible,
			);

			unset($layer->Abstract);
			unset($layer->Identifier);
			unset($layer->Name);
			unset($layer->Title);

			parseWMS_Capability_Layer($body, $layer);
		}

		if (['@attributes'] === array_keys((array)$capability->Layer)) {
			unset($capability->Layer);
		}
	}

	function parseWMS_Capability(&$body, &$capability) {
		unset($capability->Exception);
		unset($capability->ExtendedCapabilities);
		unset($capability->Request);
		unset($capability->VendorSpecificCapabilities);

		$body['assets'] = array();

		parseWMS_Capability_Layer($body, $capability);
	}

	function parseXML_OWS_11($xml, $prefix, &$body) {
		if ($xml->ServiceIdentification) {
			if ($xml->ServiceIdentification->Title) {
				$body['title'] = '' . $xml->ServiceIdentification->Title;
				unset($xml->ServiceIdentification->Title);
			}
			if ($xml->ServiceIdentification->Abstract) {
				$body['description'] = '' . $xml->ServiceIdentification->Abstract;
				unset($xml->ServiceIdentification->Abstract);
			}

			$body['keywords'] = getKeywordsWithID($xml->ServiceIdentification->Keywords);
			unset($xml->ServiceIdentification->Keywords);
			if (!(array)$xml->ServiceIdentification) unset($xml->ServiceIdentification);

			unset($xml->ServiceIdentification->AccessConstraints);
			unset($xml->ServiceIdentification->Fees);
			unset($xml->ServiceIdentification->ServiceType);
			unset($xml->ServiceIdentification->ServiceTypeVersion);
		}
		if (!(array)$xml->ServiceIdentification) unset($xml->ServiceIdentification);

		if ($xml->ServiceProvider) {
			if ($xml->ServiceProvider->ProviderName) {
				$body['provider_name'] = '' . $xml->ServiceProvider->ProviderName;
				unset($xml->ServiceProvider->ProviderName);
			}

			unset($xml->ServiceProvider->ServiceContact);
			if (!(array)$xml->ServiceProvider->ProviderSite) unset($xml->ServiceProvider->ProviderSite);
		}
		if (!(array)$xml->ServiceProvider) unset($xml->ServiceProvider);

		unset($xml->OperationsMetadata);

		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_WFS_20($xml, $prefix, &$body) {
		if ($xml->FeatureTypeList) {
			parseFeatureTypeList($body, $xml->FeatureTypeList);

			if (!(array)$xml->FeatureTypeList) {
				unset($xml->FeatureTypeList);
			}
		}

		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_XLINK($xml, $prefix, &$body) {
		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_XSD($xml, $prefix, &$body) {
		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_XSI($xml, $prefix, &$body) {
		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXML_FooNamespace($xml, $prefix, &$body) {
		if ((array)$xml) {
			$body['_'.$prefix] = $xml;
		}
	}

	function parseXMLNamespaces($xml, &$body) {
		$ns = $xml->getDocNamespaces();

		foreach($ns as $prefix => $uri) {
			if ('' !== $prefix) {
				$children = $xml->children($prefix, true);

				if ('http://www.esri.com/wms' === $uri) {
					parseXML_EsriWMS($children, $prefix, $body);
				} else if ('http://www.opengis.net/fes/2.0' === $uri) {
					parseXML_FES_20($children, $prefix, $body);
				} else if (('http://www.opengis.net/gml' === $uri) || ('http://www.opengis.net/gml/3.2' === $uri)) {
					parseXML_GML($children, $prefix, $body);
				} else if ('http://inspire.ec.europa.eu/schemas/common/1.0' === $uri) {
					parseXML_InspireCommon($children, $prefix, $body);
				} else if ('http://inspire.ec.europa.eu/schemas/inspire_dls/1.0' === $uri) {
					parseXML_InspireDLS($children, $prefix, $body);
				} else if ('http://inspire.ec.europa.eu/schemas/inspire_vs/1.0' === $uri) {
					parseXML_InspireVS($children, $prefix, $body);
				} else if ('http://www.opengis.net/ogc' === $uri) {
					parseXML_OGC($children, $prefix, $body);
				} else if ('http://www.opengis.net/ows/1.1' === $uri) {
					parseXML_OWS_11($children, $prefix, $body);
				} else if ('http://www.opengis.net/wfs/2.0' === $uri) {
					parseXML_WFS_20($children, $prefix, $body);
				} else if ('http://www.w3.org/1999/xlink' === $uri) {
					parseXML_XLINK($children, $prefix, $body);
				} else if ('http://www.w3.org/2001/XMLSchema' === $uri) {
					parseXML_XSD($children, $prefix, $body);
				} else if ('http://www.w3.org/2001/XMLSchema-instance' === $uri) {
					parseXML_XSI($children, $prefix, $body);
				} else if ('https://geoportal.saarland.de/arcgis/services/Internet/Boden_WFS/MapServer/WFSServer' === $uri) {
					parseXML_FooNamespace($children, $prefix, $body);
				} else {
					$body[] = $prefix;
					$body[] = $uri;
//					$body[] = $children;
				}
			}
		}
	}

	function parseFeatureTypeList(&$body, &$child) {
		if ($child->FeatureType) {
			$body['assets'] = array();

			foreach($child->FeatureType as $feature) {
				unset($feature->DefaultCRS);
				unset($feature->OtherCRS);
				unset($feature->OutputFormats);

				if (!(array)$feature->MetadataURL) unset($feature->MetadataURL);

				$body['assets'][] = (object) array(
					'name' => '' . $feature->Name,
					'title' => '' . $feature->Title,
					'descriptions' => '' . $feature->Abstract,
				);

				unset($feature->Abstract);
				unset($feature->Name);
				unset($feature->Title);
			}
		}

		if (count(array_filter((array) $child->FeatureType)) === 0) {
			unset($child->FeatureType);
		}
	}

	// opengis OGC, opengis OWS and opengis WMS
	function parseOGC_OWS_WMS($xml, &$body, &$error, &$contentType) {
		$rootName = $xml->getName();

		if (('ExceptionReport' === $rootName) || ('ServiceExceptionReport' === $rootName)) {
			$xml->rewind();

			$key = '';
			$values = [];
			$attributes = [];

			if ($xml->valid()) {
				// without namespace
				$key = $xml->key();
				$attributes = ((array) $xml->current())['@attributes'];

				foreach($xml->getChildren() as $name => $data) {
					$values[] = '' . $data;
				}
				if (!$values) {
					$values = ((array)$xml)[$key];
				}
			} else {
				$ns = $xml->getDocNamespaces();
				$key = 'Exception';

				foreach($ns as $prefix => $uri) {
					if ('http://www.opengis.net/ows/1.1' === $uri) {
						$children = $xml->children($prefix, true);
						$values[] = '' . $children->Exception->ExceptionText;
					}
				}
			}

			$error = (object) array(
				'type' => $rootName,
				'name' => $key,
				'code' => $attributes['exceptionCode'] . $attributes['code'],
				'descriptions' => $values,
			);
			return;
		}

		$attributes = ((array) $xml)['@attributes'];
		$version = $attributes['version'];

		$ret = [];

		if ('WFS_Capabilities' === $rootName) {
			$contentType = 'ogc:wfs';
			$ret['version'] = $version;
		} else if ('WMS_Capabilities' === $rootName) {
			$contentType = 'ogc:wms';
			$ret['version'] = $version;
		} else if ('WMT_MS_Capabilities' === $rootName) {
			$contentType = 'ogc:wms';
			$ret['version'] = $version;
		}

		parseXMLNamespaces($xml, $ret);

		for ($xml->rewind(); $xml->valid(); $xml->next()) {
			$key = $xml->key();
			$child = $xml->current();

			if ('FeatureTypeList' === $key) {
				parseFeatureTypeList($ret, $child);
			}
			if ('Service' === $key) {
				parseWMS_Service($ret, $child);
			}
			if ('Capability' === $key) {
				parseWMS_Capability($ret, $child);
			}

			if ((array)$child) {
				$ret[$key] = $child;
			}
		}

		$body = (object) $ret;
	}

	function parser($file) {
		$MAGIC_XML = '<?xml ';
		$MAGIC_HTML = '<!doctype html';
		$contentType = '';
		$body = null;
		$error = null;

		if ($MAGIC_XML === strtolower(substr($file->content, 0, strlen($MAGIC_XML)))) {
			$contentType = 'xml';
			$xml = simplexml_load_string($file->content);
			$ns = $xml->getDocNamespaces();

			if (in_array('http://www.opengis.net/ogc', $ns)) {
				parseOGC_OWS_WMS($xml, $body, $error, $contentType);
			} else if (in_array('http://www.opengis.net/ows/1.1', $ns)) {
				parseOGC_OWS_WMS($xml, $body, $error, $contentType);
			} else if (in_array('http://www.opengis.net/wms', $ns)) {
				parseOGC_OWS_WMS($xml, $body, $error, $contentType);
			} else if (in_array('http://inspire.ec.europa.eu/schemas/common/1.0', $ns)) {
				parseOGC_OWS_WMS($xml, $body, $error, $contentType);
			} else {
				$body = $ns;
	//			$body = $xml->getName();
	//			$body = $xml->getNamespaces();
	//			$body = $xml->getChildren();
	//			$body = $xml['ows:Exception'];
			}
		} else if ($MAGIC_HTML === strtolower(substr($file->content, 0, strlen($MAGIC_HTML)))) {
			$contentType = 'html';

			// to do
		}

		$ret = (object) array(
			'contentType' => $contentType,
			'error' => $error,
			'body' => $body,
		);

		return $ret;
	}
?>