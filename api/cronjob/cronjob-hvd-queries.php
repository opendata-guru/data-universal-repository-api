<?php
	function getEUcatalogGovData() {
		return 'http://data.europa.eu/88u/catalogue/govdata';

    // denmark HVD catalog: http://data.europa.eu/88u/catalogue/datavejviser
    // http://data.europa.eu/88u/catalogue/plateforme-ouverte-des-donnees-publiques-francaises
    // http://data.europa.eu/88u/catalogue/data-gov-sk
  }

	function getSPARQLgetEUcatalogs() {
		$sparql = '
prefix dct: <http://purl.org/dc/terms/>
prefix dcat: <http://www.w3.org/ns/dcat#>

select DISTINCT ?catalog ?title ?description
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
prefix dcat: <http://www.w3.org/ns/dcat#>
prefix r5r: <http://data.europa.eu/r5r/>

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
prefix dcat: <http://www.w3.org/ns/dcat#>
prefix r5r: <http://data.europa.eu/r5r/>

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
prefix dcat: <http://www.w3.org/ns/dcat#>
prefix r5r: <http://data.europa.eu/r5r/>

select (count(distinct ?api) as ?count) where {
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
prefix dcat: <http://www.w3.org/ns/dcat#>
prefix dct: <http://purl.org/dc/terms/>
prefix r5r: <http://data.europa.eu/r5r/>

select ?license (count(?license) as ?count) ?mapped where {
  <?MSCat?> ?cp ?d.
  ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?d a dcat:Dataset.
  ?d dcat:distribution ?dist.
  ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
  ?dist dct:license ?license.
  optional {
    ?license ?skos ?mapped.
    FILTER ( ?skos IN ( <http://www.w3.org/2004/02/skos/core#exactMatch>,
                        <http://www.w3.org/2004/02/skos/core#narrowMatch>,
                        <http://www.w3.org/2004/02/skos/core#broadMatch> ))
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
  // This query returns all the high-value datasets
  // harvested from a given MS. This is done by replacing
  // the parameter <?MSCat? > with the URI of the MS
  // catalogue in the DEU.
  // -----------------------------------------------------
  // same as: getSPARQLcountEUdatasetsByCatalog()
  // -----------------------------------------------------

  $sparqlQueryAllDatasets = '
    prefix dcat: <http://www.w3.org/ns/dcat#>
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct ?d  where {
      <?MSCat?> ?cp ?d.
      ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      ?d a dcat:Dataset.
    }
  ';

  // -----------------------------------------------------
  // The harvesting by the DEU performs for its own
  // purposes a harmonisation step in which the source
  // identifiers of datasets are replaced with DEU
  // specific identifiers. The original identifiers
  // provided by the harvested catalogues are maintained
  // in the catalogue records of the DEU (as a result of
  // the harvesting process). The following query
  // retrieves the original identifiers for each HVD
  // dataset so that MS can perform an internal
  // cross-check.
  // -----------------------------------------------------

  $sparqlQuerySourceIdentifiers = '
    prefix dcat: <http://www.w3.org/ns/dcat#>
    prefix dct: <http://purl.org/dc/terms/> 
    prefix foaf: <http://xmlns.com/foaf/0.1/> 
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct ?d ?originalId where {
      <?MSCat?> ?cp ?d.
      ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      ?d a dcat:Dataset.

      ?record foaf:primaryTopic ?d.
      ?record a dcat:CatalogRecord.
      ?record dct:identifier ?originalId.
    }
  ';

  // -----------------------------------------------------
  // For any high-value dataset this query provides the
  // title, description and HVD category. These are the
  // mandatory DCAT-AP HVD key metadata.
  // Note: The query returns only the English texts. These
  //   can be the result of machine translation service
  //   embedded in the DEU harvesting
  // -----------------------------------------------------
  // Findings:
  // - check only HVD marked dataset
  // - not check for HVD category (may be empty)
  // - not check distribution marked for HVD
  // - return: datasets
  // -----------------------------------------------------
  // query with    '?category' count 874 entries
  // query without '?category' count 868 entries
  // -> query only '?category' results in 8 entries
  //    5x URIs, 3x empty values
  // -----------------------------------------------------

  $sparqlQueryAllDatasetsMandatory = '
    prefix dcat: <http://www.w3.org/ns/dcat#>
    prefix dct: <http://purl.org/dc/terms/> 
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct ?d ?title ?desc ?category where {
      <?MSCat?> ?cp ?d.
      ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      ?d a dcat:Dataset.
      optional { ?d dct:title ?title.
        FILTER ( langMatches( lang(?title), "en" ))
      }
      optional { ?d dct:description ?desc.
        FILTER ( langMatches( lang(?desc), "en" ))
      }
      optional { ?d r5r:hvdCategory ?category. }
    }
  ';

  // -----------------------------------------------------
  // High-value datasets are usually subject to the
  // obligation to be provided as bulk download. This
  // assessment query will allow to detect these aspects.
  // Note: There could be multiple Distributions for one
  //   Dataset. This multiplicity is the reason that this
  //   is a separate query, and that it cannot be part of
  //   a simple table with Datasets.
  // There could be Distributions for a High Value Dataset
  // that are not considered to be reported in the context
  // of the HVD IR. It may be assumed that the collection
  // phase has removed those. But to guarantee that
  // incorrect values are not returned, the identification
  // condition is included.
  // -----------------------------------------------------
  // Findings:
  // - not check for HVD category (may be empty)
  // -----------------------------------------------------
  // same as: getSPARQLcountEUdistributionsByCatalog()
  // -----------------------------------------------------

  $sparqlQueryDistributions = '
    prefix dcat: <http://www.w3.org/ns/dcat#>
    prefix dct: <http://purl.org/dc/terms/>
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct ?d ?dist ?title ?accessURL where {
      <?MSCat?> ?cp ?d.
      ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      ?d a dcat:Dataset.
      ?d dcat:distribution ?dist.
      ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      optional { ?dist dct:title ?title.
        FILTER ( langMatches( lang(?title), "en" ))
      }
      optional { ?dist dcat:accessURL ?accessURL. }
    }
  ';

  // -----------------------------------------------------
  // APIs are one of the main obligations imposed on the
  // HVDs by the Implementing Regulation. APIs are denoted
  // in DCAT-AP HVD with Data Services. DCAT-AP Data
  // Services can be associated in two distinct ways with
  // a Dataset. This query explores both.
  // -----------------------------------------------------
  // same as: getSPARQLcountEUdataServicesByCatalog()
  // -----------------------------------------------------

  $sparqlQueryDataServices = '
    prefix dcat:  <http://www.w3.org/ns/dcat#>
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct ?d ?api where {
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

  // -----------------------------------------------------
  // APIs must be provided with sufficient information.
  // -----------------------------------------------------

  $sparqlQueryDataServicesInformation = '
    prefix dcat:  <http://www.w3.org/ns/dcat#>
    prefix dct: <http://purl.org/dc/terms/>
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct ?d ?api ?title ?desc ?category ?endpointURL ?endpointDesc where {
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
      optional { ?api dct:title ?title.
        FILTER ( langMatches( lang(?title), "en" ))
      }
      optional { ?api dct:description ?desc.
        FILTER ( langMatches( lang(?desc), "en" ))
      }
      optional { ?api r5r:hvdCategory ?category. }
      optional { ?api dcat:endpointDescription ?endpointDesc. }
      optional { ?api dcat:endpointURL ?endpointURL. }
    }
  ';

  // -----------------------------------------------------
  // High-value datasets must be made available under a
  // permissive licence, such as Creative Commons BY 4.0.
  // In DCAT-AP the legal information is associated with
  // the ‘Distributions’ and Data Services associated with
  // the Datasets. Because legal information is an
  // important aspect of the HVD IR, a specific reporting
  // query is provided.
  // Note: Legal information in DCAT-AP is a combination
  //   of three properties: the access rights, the
  //   licences and the rights. 'Access rights’ provides a
  //   condensed view on the limitations that restrict
  //   access to data. ‘Licences’ and ‘rights’ are the
  //   legal conditions on the use or reuse of the data.
  // -----------------------------------------------------
  // Findings:
  // - it's only for APIs!
  // -----------------------------------------------------

  $sparqlQueryLicencesDataService = '
    prefix dcat: <http://www.w3.org/ns/dcat#>
    prefix dct: <http://purl.org/dc/terms/>
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct ?d ?api ?title ?lic ?rights where {
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
      optional { ?api dct:title ?title.
        FILTER ( langMatches( lang(?title), "en" ))
      }
      OPTIONAL { ?api dct:license ?lic. }
      OPTIONAL { ?api dct:rights ?rights. }
    }
  ';

  // -----------------------------------------------------
  // The licences that are provided must according to the
  // HVD IR satisfy a number of quality requirements:
  // - A licence must be provided in human and
  //   machine-readable format.
  // - A licence must be provided with a persistent URI.
  // - A licence must be at least as permissive as
  //   CC-BY 4.0.
  // -----------------------------------------------------
  // Findings:
  // - it's only for distributions
  // -----------------------------------------------------

  $sparqlQueryLicencesDistribution = '
    prefix dcat: <http://www.w3.org/ns/dcat#>
    prefix dct: <http://purl.org/dc/terms/>
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct ?d ?dist ?title ?lic ?rights where {
      #<?MSCat?> ?cp ?d.
      <http://data.europa.eu/88u/catalogue/govdata> ?cp ?d.
      ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      ?d a dcat:Dataset.
      ?d dcat:distribution ?dist.
      ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      optional { ?dist dct:title ?title.
        FILTER ( langMatches( lang(?title), "en" ))
      }
      OPTIONAL { ?dist dct:license ?lic. }
      OPTIONAL { ?dist dct:rights ?rights. }
    }
  ';

  // -----------------------------------------------------
  // This reporting query will assist the assessment
  // whether the provided licences are in line with the
  // last aspect. In DCAT-AP HVD it is recommended that
  // for the reporting the MS HVD contact provides a
  // mapping from all reported licences to the EU
  // Vocabularies NAL (Name Authority List) licences. This
  // query takes that knowledge into account. This
  // recommendation allows for a quick assessment of
  // permissiveness as compared to CC-BY 4.0. If no
  // licence is provided, the provided rights will be
  // investigated for those quality requirements. Since
  // rights express usually a single aspect of reuse, this
  // investigation is more complicated. In particular,
  // there is no consolidated controlled vocabulary of
  // rights available, to which one could match the
  // specific rights provided in a MS. For that reason, no
  // specific query for rights has been provided in this
  // version of the document. The previous query 6 will
  // check the presence (licence and/or rights) of legal
  // information.
  // -----------------------------------------------------
  // Findings:
  // - licenses only from distributions
  // -----------------------------------------------------
  // same as: getSPARQLcountEUlicensesByCatalog
  // -----------------------------------------------------

  $sparqlQueryLicencesOnly = '
    prefix dcat: <http://www.w3.org/ns/dcat#>
    prefix dct: <http://purl.org/dc/terms/>
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct ?lic ?skos ?mapped where {
      <?MSCat?> ?cp ?d.
      ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      ?d a dcat:Dataset.
      ?d dcat:distribution ?dist.
      ?dist r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      ?dist dct:license ?lic.
      optional {
        ?lic ?skos ?mapped.
        FILTER ( ?skos IN ( <http://www.w3.org/2004/02/skos/core#exactMatch>,
                            <http://www.w3.org/2004/02/skos/core#narrowMatch>,
                            <http://www.w3.org/2004/02/skos/core#broadMatch> ) )
      }
    }
  ';

  // -----------------------------------------------------
  // Findings:
  // - licenses only from data services
  // -----------------------------------------------------

  $sparqlQueryLicencesOnly2 = '
    prefix dcat: <http://www.w3.org/ns/dcat#>
    prefix dct: <http://purl.org/dc/terms/>
    prefix r5r: <http://data.europa.eu/r5r/>

    select distinct ?lic ?skos ?mapped where {
      #<?MSCat?> ?cp ?d.
      <http://data.europa.eu/88u/catalogue/govdata> ?cp ?d.
      ?d r5r:applicableLegislation <http://data.europa.eu/eli/reg_impl/2023/138/oj>.
      ?d a dcat:Dataset.
      ?api a dcat:DataService.
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
      ?api dct:license ?lic.
      optional {
        ?lic ?skos ?mapped.
        FILTER ( ?skos IN ( <http://www.w3.org/2004/02/skos/core#exactMatch>,
                            <http://www.w3.org/2004/02/skos/core#narrowMatch>,
                            <http://www.w3.org/2004/02/skos/core#broadMatch> ) )
      }
    }
  ';

  // -----------------------------------------------------
  // It cannot be excluded that the collected HVD metadata
  // is only partially complete. This can have several
  // reasons:
  // - The MS HVD contact did not ensure that all metadata
  //   is available in the DEU.
  // - The MS has not fulfilled its obligations according
  //   to the HVD IR and therefore information is missing.
  // - The HVD IR has distinct requirements per kind of
  //   High Value Dataset that might result in collected
  //   metadata appearing to be incomplete, while still
  //   being in compliance with the HVD IR.
  //
  // To detect these cases the collected HVD metadata must
  // be assessed for completeness.
  //
  // As it is challenging to detect metadata
  // incompleteness via SPARQL queries, an alternative
  // method is used: a SHACL validation. The following
  // SHACL template is an extract of the SHACL, which will
  // find Datasets that have no Data Service associated
  // with them. Given the fact that in general high-value
  // datasets should have a Data Service associated with
  // them, the absence of a Data Service might indicate an
  // issue.
  // -----------------------------------------------------

  $shacl = '';

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