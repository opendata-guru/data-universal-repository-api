# data-universal-repository-api

Data Universal Repository API

## API description

Use the api rendering at [opendata.guru](https://opendata.guru/govdata/api.html).

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

https://www.adv-online.de/AdV-Produkte/INSPIRE/binarywriterservlet?imgUid=8cf405d5-71de-0f51-09b3-266605d1cbf2&uBasVariant=11111111-1111-1111-1111-111111111111

## CSW ideas

https://www.geodaten.niedersachsen.de/startseite/inspire/suchdienste/suchdienste-csw-fuer-inspire-147897.html

- https://geoportal.bayern.de/csw/gdi?REQUEST=GetRecordById&VERSION=2.0.2&service=CSW&outputschema=csw:IsoRecord&elementsetname=full&ID=1e025cc4-d4b1-378e-9924-45950aef2334
- https://geoportal.bayern.de/csw/gdi?service=CSW&version=2.0.2&request=GetRecords&namespace=xmlns(csw=http://www.opengis.net/cat/csw/2.0.2),xmlns(gmd=http://www.isotc211.org/2005/gmd)&resultType=results&outputFormat=application/xml&outputSchema=http://www.isotc211.org/2005/gmd&startPosition=1&maxRecords=1&typeNames=csw:Record&elementSetName=full&constraintLanguage=CQL_TEXT&constraint_language_version=1.1.0&constraint=csw:ResourceIdentifier=%27*85e438ab-d4ee-3d81-97b4-4fda4def34e1*%27
- https://geoportal.saarland.de/gdi-sl/csw?SERVICE=CSW&VERSION=2.0.2&REQUEST=GetRecordById&ELEMENTSETNAME=full&OUTPUTSCHEMA=http://www.isotc211.org/2005/gmd&ID=85DE1D1C-FB80-4EB1-8B21-F090A7B02DBB
- https://geoportal.brandenburg.de/csw-gdi-bb/service?SERVICE=CSW
- https://geoportal.bgr.de/smartfindersdi-csw/api?service=CSW&version=2.0.2&request=GetRecordById&Id=AC3815DC-663E-4325-9283-CAC631989F15

/csw/api

- https://geoportal.bafg.de/csw/api
- https://geoportal.bafg.de/csw/api?request=GetCapabilities&service=CSW

/srv/ger/csw

- https://geoportal.geodaten.niedersachsen.de/harvest/srv/ger/csw?
- https://www.geoportal-bw.de/geonetwork/srv/ger/csw?REQUEST=GetCapabilities&SERVICE=CSW&version=2.0.2
- https://www.geoportal-bw.de/geonetwork/srv/ger/csw?elementSetName=full&id=3cec2f8b-3f80-4aa4-9d4c-922b0dc7eb6c&outputSchema=http://www.isotc211.org/2005/gmd&request=GetRecordById&service=CSW&version=2.0.2
- https://geomis.geoportal-th.de/geonetwork/srv/ger/csw?SERVICE=CSW&REQUEST=GetCapabilities
- https://geomis.geoportal-th.de/geonetwork/srv/ger/csw?REQUEST=GetRecordById&SERVICE=CSW&VERSION=2.0.2&Elementsetname=full&outputSchema=http://www.isotc211.org/2005/gmd&ID=f6c3c5de-3530-4fbe-95f2-e5fe1188cf62
- https://geoportal.muenchen.de/geonetwork/srv/ger/csw?service=CSW&request=GetRecordById&version=2.0.2&elementsetname=full&id=5852eba1-691a-42e1-9fb0-2311d395e25e
- https://daten.geoportal.ruhr/srv/ger/csw?request=GetRecordById&service=CSW&version=2.0.2&elementSetName=full&id=87913d0e-57b1-4497-84a6-83458d62d007

## Contributions

### Add a new system

To implement a new system (data portal or file format) follow these steps:

Essential:

- in `helper/_link.php` add an url pattern matching or content analyses
- add file `live-countdatasets/live-countdatasets-{name}.php` to count datasets of the system
- in `live-countdatasets.php` add a link to the new system file

Nice to have:

- in `openapi.json` and `openapi.yaml` add an example to `components -> parameters -> linkParam`
- add file `system/system-{name}.php` to collect metadata of the system
- in `system.php` add a link to the new system file

todo (for documentation)

- SYSTEMS.HTML or .JS
- LIVE HARVESTER
- SUPPLIERS
- SYSTEM CHANGELOG
