<?php
	function getEUcatalogGovData() {
		return 'http://data.europa.eu/88u/catalogue/govdata';

    // denmark HVD catalog: http://data.europa.eu/88u/catalogue/datavejviser
  }

	function getSPARQLgetEUcatalogs() {
		$sparql = '
prefix dct: <http://purl.org/dc/terms/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select ?catalog ?title ?description
where {
  ?catalog a dcat:Catalog.
  ?catalog dct:title ?title.
  ?catalog dct:description ?description.
}
		';

		return $sparql;
	}

	function getSPARQLcountEUdatasetsByCatalog($catalog) {
		$sparql = '
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select (count(?d) as ?count) where {
  <?MSCat?> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?d a dcat:Dataset.
}
		';

		return str_replace('?MSCat?', $catalog, $sparql);
	}

	function getSPARQLcountEUdistributionsByCatalog($catalog) {
		$sparql = '
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select (count(?dist) as ?count) where {
  <?MSCat?> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?d a dcat:Dataset.
  ?d dcat:distribution ?dist.
  ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
}
		';

		return str_replace('?MSCat?', $catalog, $sparql);
	}

	function getSPARQLcountEUaccessServicesByCatalog($catalog) {
		$sparql = '
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select (count(distinct ?d) as ?countDatasets) (count(distinct ?dist) as ?countDist) (count(distinct ?api) as ?countAPI) where {
  <?MSCat?> ?cp ?d.
  ?d a dcat:Dataset.
  ?d dcat:distribution ?dist.
  ?dist dcat:accessService ?api.
  ?api r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
}
		';

		return str_replace('?MSCat?', $catalog, $sparql);
	}

	function getSPARQLgetEUaccessURLsByCatalog($catalog) {
		$sparql = '
prefix dct: <http://purl.org/dc/terms/>
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select ?identifier ?accessURL where {
  <?MSCat?> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?d a dcat:Dataset.
  ?d dct:identifier ?identifier.
  ?d dcat:distribution ?dist.
  ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.

  optional { ?dist dcat:accessURL ?accessURL. }
}
		';

		return str_replace('?MSCat?', $catalog, $sparql);
	}

	function getSPARQLgetEUaccessServicesByCatalog($catalog) {
		$sparql = '
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select distinct ?api where {
  <?MSCat?> ?cp ?d.
  ?d a dcat:Dataset.
  ?d dcat:distribution ?dist.
  ?dist dcat:accessService ?api.
  ?api r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
}
		';

		return str_replace('?MSCat?', $catalog, $sparql);
	}

	// -----------------------------------------------------
	// currently known SPARQL queries: none
	// future publication site: https://dataeuropa.gitlab.io/data-provider-manual/hvd/sparql/
	// meanwhile: use some queries show on event "Reporting High Value Datasets using DCAT-AP HVD and DEU" from SEMIC at 2024-06-04
	// -----------------------------------------------------

	// -----------------------------------------------------
	// Test SPARQL search at https://data.europa.eu/data/sparql?locale=en
	// -----------------------------------------------------

// -----------------------------------------------------
// 0. Determining the MS reporting scope
$sparql0 = '
construct {?s ?p ?o.
           ?dist ?distp ?disto.
           ?distapi ?distapip ?distapio.
           ?API ?APIp ?APIo.
} where {
# <?MSCat?> ?cp ?s.
  <http://data.europa.eu/88u/catalogue/govdata> ?cp ?s.
?s <http://data.europa.eu/r5r/applicableLegislation> <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
{ ?s ?p ?o. }
union {
  ?s <http://www.w3.org/ns/dcat#distribution> ?dist.
  ?dist ?distp ?disto.
  ?dist <http://data.europa.eu/r5r/applicableLegistlation> <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
}
union {
  ?dist <http://www.w3.org/ns/dcat#accessService> ?distapi.
  ?distapi ?distapip ?distapio.
  ?distapi <http://data.europa.eu/r5r/applicableLegistlation> <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
}
union {
  ?API <http://www.w3.org/ns/dcat#servesDataset> ?s.
  ?API ?APIp ?APIo.
  ?API <http://data.europa.eu/r5r/applicableLegistlation> <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
}
} limit 10
';

// -----------------------------------------------------
// 1) Reporting High Value Datasets with key information
// - check only HVD marked dataset
// - not check for HVD category (may be empty)
// - not check distribution marked for HVD
// - return: datasets
// -----------------------------------------------------
// same as: getSPARQLcountEUdatasetsByCatalog()
// -----------------------------------------------------
// query with    '?Category' count 874 entries
// query without '?Category' count 868 entries
// -> query only '?Category' results in 8 entries
//    5x URIs, 3x empty values
// -----------------------------------------------------
$sparql1 = '
prefix dct: <http://purl.org/dc/terms/>
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select distinct ?d ?title ?desc ?Category where {
#  <?MSCat?> ?cp ?d.
  <http://data.europa.eu/88u/catalogue/govdata> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?d a dcat:Dataset.
  optional { ?d dct:title ?title.
    FILTER ( lang(?title) = "en" )
  }
  optional { ?d dct:description ?desc.
    FILTER ( lang(?desc) = "en" )
  }
  optional { ?d r5r:hvdCategory ?Category. }
} limit 10
';

// ----------------------------------------------------------------------------------------
// 2) Reporting Bulk Downloads (Distributions) for High Value Datasets with key information
// - check HVD marked dataset and HVD marked distributions
// - not check for HVD category (may be empty)
// - return: distributions
// ----------------------------------------------------------------------------------------
// same as: getSPARQLcountEUdistributionsByCatalog()
// ----------------------------------------------------------------------------------------
$sparql2 = '
prefix dct: <http://purl.org/dc/terms/>
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select distinct ?d ?dist ?title ?accessURL where {
#  <?MSCat?> ?cp ?d.
  <http://data.europa.eu/88u/catalogue/govdata> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?d a dcat:Dataset.
  ?d dcat:distribution ?dist.
  ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  optional { ?dist dct:title ?title.
    FILTER ( lang(?title) = "en" )
  }
  optional { ?dist dcat:accessURL ?accessURL. }
}
';

// -----------------------------------------------------------------------------
// 3) Reported APIs (Data Services) for High Value Datasets with key information
// !!! Query is broken !!!
// -----------------------------------------------------------------------------
$sparql3 = '
prefix dct: <http://purl.org/dc/terms/>
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select distinct ?d ?api ?title ?desc ?category ?endpointURL ?endpointDesc where {
#  <?MSCat?> ?cp ?d.
  <http://data.europa.eu/88u/catalogue/govdata> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  {
    ?d dcat:distribution ?dist.
    ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.

    ?dist dcat:accessService ?api.
    ?api r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  }
  union {
    ?api dcat:servesDataset ?d.
    ?api r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  }
  optional { ?api dct:title ?title.
    FILTER ( lang(?title) = "en" )
  }
  optional { ?api dct:description ?desc.
    FILTER ( lang(?desc) = "en" )
  }
  optional { ?api r5r:hvdCategory ?category. }
  optional { ?api dcat:endpointDescription ?endpointDesc. }
  optional { ?api dcat:endpointURL ?endpointURL. }
}
';

// -------------------------------------------------------
// 4) Reported legal information on Distributions and APIs
// !!! Query is broken !!!
// -------------------------------------------------------
$sparql4 = '
prefix dct: <http://purl.org/dc/terms/>
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select distinct ?d ?api ?title ?lic ?rights where {
#  <?MSCat?> ?cp ?d.
  <http://data.europa.eu/88u/catalogue/govdata> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  {
    ?d dcat:distribution ?dist.
    ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.

    ?dist dcat:accessService ?api.
    ?api r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  }
  union {
    ?api dcat:servesDataset ?d.
    ?api r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  }
  optional { ?api dct:title ?title.
    FILTER ( lang(?title) = "en" )
  }
  optional { ?api dcat:license ?lic. }
  optional { ?api dcat:rights ?rights. }
}
';

// --------------------------------------------------
// 5) Reported legal information on provided licences
// --------------------------------------------------
$sparql5 = '
PREFIX dc: <http://purl.org/dc/elements/1.1/>
prefix dct: <http://purl.org/dc/terms/>
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select distinct ?lic ?skos ?mapped where {
#  <?MSCat?> ?cp ?d.
  <http://data.europa.eu/88u/catalogue/govdata> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?d a dcat:Dataset.
  ?d dcat:distribution ?dist.
  ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  optional { ?dist dct:title ?title.
    FILTER ( lang(?title) = "en" )
  }

  OPTIONAL { ?dist dct:license ?lic.
    Optional {
      ?lic ?skos ?mapped.
      FILTER ( ?skos IN ( <http://www.w3.org/2004/02/skos/core#exactMatch>,
                          <http://www.w3.org/2004/02/skos/core#narrowMatch>,
                          <http://www.w3.org/2004/02/skos/core#broadMatch> ))
    }
  }
}
';

// ------------------------------------------
// 6) Report queries for completeness (SHACL)
// ------------------------------------------
$sparql6 = '
';

// ------------------------------------------
// Test: Count HVD categories
// ------------------------------------------
$sparqlTest1 = '
prefix dct: <http://purl.org/dc/terms/>
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select ?hvdCategory (count(?hvdCategory) as ?count) where {
#  <?MSCat?> ?cp ?d.
  <http://data.europa.eu/88u/catalogue/govdata> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?d a dcat:Dataset.
  ?d r5r:hvdCategory ?hvdCategory.
}
';

// ------------------------------------------
// Test
// ------------------------------------------
$sparqlTest2 = '
prefix dct: <http://purl.org/dc/terms/>
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select distinct ?MSCat (count(?MSCat) as ?count) where {
  ?MSCat ?cp ?d.
  FILTER(REGEX(STR(?MSCat), "^http://data.europa.eu/88u/catalogue/"))

  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?d a dcat:Dataset.
  ?d r5r:hvdCategory ?hvdCategory.

} limit 25
';

?>