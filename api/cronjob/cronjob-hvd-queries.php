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

	function getSPARQLcountEUdataServicesByCatalog($catalog) {
    $sparql = '
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select (count(?api) as ?count) where {
  <?MSCat?> ?cp ?d.
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

  function getSPARQLcountEUlicensesByCatalog($catalog) {
		$sparql = '
prefix dct: <http://purl.org/dc/terms/>
prefix r5r: <http://data.europa.eu/r5r/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select ?license (count(?license) as ?count) ?mapped where {
  <?MSCat?> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?d a dcat:Dataset.
  ?d dcat:distribution ?dist.
  ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  OPTIONAL { ?dist dct:license ?license.
    OPTIONAL { ?license ?skos ?mapped.
      FILTER ( ?skos IN ( <http://www.w3.org/2004/02/skos/core#exactMatch>,
                          <http://www.w3.org/2004/02/skos/core#narrowMatch>,
                          <http://www.w3.org/2004/02/skos/core#broadMatch> ))
    }
  }
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
  // currently known SPARQL queries:
  // https://dataeuropa.gitlab.io/data-provider-manual/hvd/Reporting_guidelines_for_HVDs/
  //
  // Test SPARQL search at
  //  https://data.europa.eu/data/sparql?locale=en
  // -----------------------------------------------------

  // -----------------------------------------------------
  // In case one has to lookup the MS catalogue URI, to
  // fill in the parameter <?MSCat? >, the following query
  // can be applied. It results in all catalogues having a
  // resource that is indicated to be published according
  // to the HVD IR.
  // -----------------------------------------------------

  $sparqlQueryMSCat = '
    prefix dcat: <http://www.w3.org/ns/dcat#>
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct (?c as ?MSCat)  where {
      ?s r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      ?c a dcat:Catalog.
      ?c ?p ?s.
    } group by ?c
  ';

  // -----------------------------------------------------
  // The construction query below creates a snapshot of a
  // MS HVD catalogue. To execute the query, the user must
  // replace the parameter <?MScat? > with the MS HVD
  // catalogue URI in the DEU. As the amount of data
  // returned may be over the allowed number of results by
  // the sparql endpoint, pagination must be applied to
  // download the whole snapshot. Pagination is done by
  // the query elements:
  // - Limit: the size of a pagination. Max 50000, but to
  //          avoid other
  // - Offset: the startpoint of the page.
  //
  // Users must incrementally increase the offset value
  // until the result is empty. The concatenation of all
  // the downloaded files is the snapshot.
  // -----------------------------------------------------

  $sparqlQuerySnapshot = '
    prefix dcat: <http://www.w3.org/ns/dcat#>
    prefix r5r: <http://data.europa.eu/r5r/>

    construct {?s ?p ?o.
              ?dist ?distp ?disto.
              ?distapi ?distapip ?distapio.
              ?API ?APIp ?APIo.
    } where {
      <?MSCat?> ?cp ?s.
      ?s r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      { ?s ?p ?o. }
      union {
        ?s dcat:distribution ?dist.
        ?dist ?distp ?disto.
        ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      }
      union {
        ?s dcat:distribution ?dist.
        ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
        ?dist dcat:accessService ?distapi.
        ?distapi ?distapip ?distapio.
        ?distapi r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      }
      union {
        ?API dcat:servesDataset ?s.
        ?API ?APIp ?APIo.
        ?API r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
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
// - result in 0 entries
// -----------------------------------------------------------------------------
// same as: getSPARQLcountEUdataServicesByCatalog()
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
// - it's only for APIs!
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
// - licenses only from distributions
// --------------------------------------------------
// same as: getSPARQLcountEUlicensesByCatalog
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
// Test, please have look also at
// $sparqlQueryMSCat
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