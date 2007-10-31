'default_renderer' => 'simple',
'default_renderer_src' => null,

// TinyMCE Defaults
// The Theme is only used if the config snippet is not set (an invalid snippet name
// will be silently ignored).
'tinymce_default_theme' => 'simple',
'tinymce_default_config_snippet' => "file://midcom/helper/datamanager2/config/tinymce_default",
'tinymce_use_compressor' => false,
//'tinymce_default_config_snippet' => "{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/midcom.helper.datamanager2/tinymce",

'include_metadata_required' => false,
/* This is passed to sprintf, use %s where file name should be */
'type_blobs_avscan_command' => '',
/* Example command using ClamAV
'type_blobs_avscan_command' => 'clamscan --stdout -i --no-summary %s',
*/
