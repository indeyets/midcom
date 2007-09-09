/**
 * This is the configuration file for the module.
 * This is where you set default values for different configuration variables
 */
/* The schema databases to use */
'schemadb_product' => 'file:/org/openpsa/products/config/schemadb_product_default.inc',
'schemadb_group' => 'file:/org/openpsa/products/config/schemadb_group_default.inc',
'schemadb_businessarea' => 'file:/org/openpsa/products/config/schemadb_businessarea_default.inc',

// Whether to enable adding subcomponents to products
'enable_components' => true,
'root_group' => 0,
'root_businessarea' => 0,

// Search related
'search_first' => false,
'search_default_to_all' => true,
// How the search results should be indexed. You can use metadata and linked options, for example metadata.score
'search_index_order' => array('productGroup', 'code'),

// How to list products when browsing groups
'products_listing_order' => array('code', 'title'),

'group_list_products' => true,
'products_per_page' => 30,
'show_items_in_feed' => 10,

'api_products_enable' => true,
'api_products_schema' => 'default',

// Navigation
'display_navigation' => true,
