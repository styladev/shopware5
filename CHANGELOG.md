### v5.7.3 2021-03-30
* Provide brand (supplier) information in single product endpoint

### v5.7.2 2020-07-07
* Use proper parameters for prepared sql statements

### v5.7.1 2020-06-24
* improved controller selection
* improved output of div element and js snippet
* set status code only on seo api response
* added fallback for robots header to index,follow

### v5.7.0 2020-03-19
* Adjusted template block logic:
  * Moved respective headers in correct blocks: `frontend_index_header_canonical`, `frontend_index_header_meta_description`, `frontend_index_header_meta_tags_opengraph`, `frontend_index_header_meta_robots` and , `frontend_index_header_hreflangs`
  * Append other headers to block: `frontend_index_header_meta_tags`

### v5.6.0 2020-03-19
* Support new loading mechanism for styla content
* Please don't update with the old magazine to this plugin, please contact your styla csm before

### v5.5.2 2019-10-23
* Remove data-rootpath

### v5.5.1 2019-10-18
* Use currency factor for price calculations
* Added support for variant pseudo prices

### v5.5.0 2019-07-26
* Pass full pathname as path to seo api

### v5.4.4 2019-04-25
* Use proper 'EK' prices instead of first price
* Improved product transformation

### v5.4.3 2019-01-25
* Fixed placement of seo html

### v5.4.2 2019-01-09
* Improved resilience of product search

### v5.4.1 2019-01-08
* Ignore inactive products

### v5.4.0 2018-04-04
* Added support for modular content in product detail page
* Improved PHP 7 compatibility
* Improved rendering of SEO tags in head
* Detailed error message when product is not available
* Fallback logic if request to SEO API fails

### v5.3.1 2018-03-08
* Removed duplicate canonical tag

### v5.3.0 2018-01-18
* Added new parameter 'images' in feed endpoint to select method for retrieving images (v1, v2 or v3)

### v5.2.9 2018-01-10
* Added multiple product images in feed endpoint
* Returning SKU in feed endpoint

### v5.2.8 2017-11-10
* Added support for multiple currencies
* Code refactoring with simplified controller

### v5.2.7 2017-08-03
* Added short description field in product endpoint response
* Added categories ids field in product endpoint response

### v5.2.6 2017-06-16
* Updated Styla Logo

### v5.2.5 2017-05-31
* Added URL endpoint to fetch plugin version number: /styla-plugin-version

### v5.2.4 2017-05-15
* Added PHP7 support

### v5.2.3 2017-05-10
* Mobile hamburger menu fix for magazine pages

### v5.2.2 2017-05-02
* Added support for the "sales/abverkauf" checkbox in the inventory so that saleable/not saleable logic in magazine works same as in Shopware
* Fixed bug on version prices so that now prices per size/colour variant are updated correctly on the "add to cart" overlay in magazine
* Added VAT calculation for the variant prices so that VAT is always included

### v5.2.1 2016-11-29
* Saleable fix for variants

### v5.2.0 2016-10-24
* Added SEO Pagination
* Added SEO Status Code

### v5.1.0 2016-09-20
* Added SEO API support

### v5.0.5 2016-08-23
* Allow different domains per store view

### v5.0.4 2016-08-10
* Improved meta header implementation

### v5.0.3 2016-07-29
* Allow query parameters on magazine pages

### v5.0.2 2016-07-04
* Allow search by article number and sku
