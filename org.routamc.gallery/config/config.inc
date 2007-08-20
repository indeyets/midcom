// Related to navigation
'navigation_items' => 10,

'photostream' => null,
'photos_per_page' => 20,

// Related to schemas
'schemadb_config' => 'file:/org/routamc/gallery/config/schemadb_config.inc',
'schemadb' => 'file:/org/routamc/photostream/config/schemadb_default.inc',

'gallery_type' => null,
/* sorting by multilang linked fields has issues
'index_order' => array('photo.taken', 'photo.title'),
*/
'index_order' => array('metadata.score reverse', 'photo.taken reverse'),

// Use AJAX for editing
'enable_ajax_editing' => true,
