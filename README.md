# data-universal-repository-api

Data Universal Repository API

## API description

Just for documentation: Here are the API descriptions for the APIs used in this module.

 System name | API description
-------------|-----------------
CKAN         | [Open API](https://www.data.gv.at/api-hub/?schema=https://www.data.gv.at/katalog/schema.yml) from data.gv.at + [API Doc](https://docs.ckan.org/en/2.10/)
DKAN         | [Open API](https://demo.getdkan.org/api) + [GitHub](https://github.com/GetDKAN/dkan) + [API 1-7 description](https://github.com/GetDKAN/dkan/blob/7.x-1.x/docs/apis/ckan-dataset.md)
EntryStore   | [Open API](https://entrystore.org/api/)
Opendatasoft | [Open API](https://help.opendatasoft.com/apis/ods-explore-v2/)
Piveau       | [Open API hub-search](https://open.bydata.de/api/hub/search/) + [Open API hub-repo](https://open.bydata.de/api/hub/repo/)
mobilithek   | [FAQ](https://mobilithek.info/help/FAQ)
Spain portal | [Open API](https://datos.gob.es/en/apidata)

https://guides.data.gouv.fr/guide-data.gouv.fr/api/reference
https://data.gov.cz/api/v1/solr/info

https://data.gov.cz/api/v1/solr/info
https://data.gov.cz/api/v2/dataset?language=en&keywordLimit=14&publisherLimit=14&fileTypeLimit=14&dataServiceTypeLimit=14&themeLimit=14&isPartOfLimit=0&offset=0&limit=10&sort=title%20asc
https://data.gov.cz/api/v2/init-data?language=en
https://data.gov.cz/api/v2/label/item?iri=http%3A%2F%2Fpublications.europa.eu%2Fresource%2Fauthority%2Fdata-theme%2FGOVE&language=en
https://data.gov.cz/api/v2/label/item?iri=http%3A%2F%2Fpublications.europa.eu%2Fresource%2Fauthority%2Fdata-theme%2FECON&language=en
https://data.gov.cz/api/v2/label/item?iri=http%3A%2F%2Fpublications.europa.eu%2Fresource%2Fauthority%2Ffile-type%2FKML&language=en
https://data.gov.cz/api/v2/label/item?iri=http%3A%2F%2Fwww.w3.org%2Fns%2Fdcat%23DataService&language=en

- https://search.geobasis-bb.de/documentation/de/#api
- https://www.ingrid-oss.eu/latest/components/interface_search.html#opensearch-schnittstelle
- https://www.ingrid-oss.eu/latest/components/interface_csw.html
- https://www.ingrid-oss.eu/latest/components/interface_ige.html
- https://datos.gob.es/es/apidata

## INSPIRE

The INSPIRE XML schemas are documented here: [inspire.ec.europa.eu/schemas](https://inspire.ec.europa.eu/schemas/index.html). In [readme.txt](https://inspire.ec.europa.eu/schemas/readme.txt) a reference to the [GitHub repository](https://github.com/INSPIRE-MIF/application-schemas) is documented.

The INSPIRE registry is located here: [inspire.ec.europa.eu/registry](https://inspire.ec.europa.eu/registry). The [INSPIRE feature concept dictionary](https://inspire.ec.europa.eu/featureconcept) contain e.g. the [Existing Land Use Data Set](https://inspire.ec.europa.eu/featureconcept/ExistingLandUseDataSet).

## Add a new system

To implement a new system (data portal or file format) follow these steps:

- `helper/_link.php` and add an url pattern matching or content analyses
- `system.php` and add a link to new system file
- add file `system/system-{name}.php` to collect metadata of the system
