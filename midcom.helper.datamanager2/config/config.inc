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

'fck_static_toolbar_sets' => array
(
    'Default',
    'Basic',
),
'fck_default_toolbar_set_contents' => array
(
    'dm2' => array
    (
        array( 'Bold', 'Italic', 'Underline', 'StrikeThrough', '-', 'OrderedList', 'UnorderedList', '-', 'Outdent', 'Indent' ),
        array( 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull' ),
        array( 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteWord', ),
        array( 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'),
        array( 'Print', '-', 'FitWindow', '-', 'Source' ),
        '/',
        array( 'FontFormat' ),
        array( 'Link', 'Unlink', 'Anchor' ),
        array( 'Image', 'Flash', 'SpecialChar'),
    ),
    'simple' => array
    (
        array( 'Bold', 'Italic', 'Underline', 'StrikeThrough', '-', 'OrderedList', 'UnorderedList', '-', 'Outdent', 'Indent' ),
        array( 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull' ),
        array( 'FitWindow', '-', 'Source' ),
        '/',
        array( 'Link', 'Unlink', 'Anchor' ),
        array( 'Image', 'SpecialChar'),
    ),
    'abstract' => array
    (
        array( 'Bold', 'Italic', '-', 'OrderedList', 'UnorderedList', '-', 'Link', 'Unlink' ),
    ),
    'all' => array
    (
        array( 'Source','DocProps','-','Save','NewPage','Preview','-','Templates' ),
        array( 'Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck' ),
        array( 'Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat' ),
        array( 'Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField' ),
        '/',
        array( 'Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript' ),
        array( 'OrderedList','UnorderedList','-','Outdent','Indent' ),
        array( 'JustifyLeft','JustifyCenter','JustifyRight','JustifyFull' ),
        array( 'Link','Unlink','Anchor' ),
        array( 'Image','Flash','Table','Rule','Smiley','SpecialChar','PageBreak'),
        '/',
        array( 'Style','FontFormat','FontName','FontSize' ),
        array( 'TextColor','BGColor' ),
        array( 'FitWindow','-','About' ),
    ),
),
'fck_default_config_snippet' => 'file://midcom/helper/datamanager2/config/fckeditor_default.inc',
