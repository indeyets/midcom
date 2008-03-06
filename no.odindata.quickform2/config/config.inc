'schemadb' => 'file:/no/odindata/quickform2/config/schemadb_default.inc',
'schemadb_config' => 'file:/no/odindata/quickform2/config/schemadb_config.inc',

'breadcrumb' => '',

'mail' => true,
'mail_address_from' => '',
'mail_address_to' => '',
'mail_reply_to' => '',
'mail_subject' => '',
'mail_subject_reciept' => null,

'mail_encoding' => 'UTF-8',
'mail_submitter' => false,
'end_message'  => '',
'mail_submitter_message' => '',
'form_description' => '',
'mail_reciept' => false,
'mail_reciept_message' => '',
'mail_reciept_data' => '',

'mail_class' => 'no_odindata_quickform2_emailgenerator',
'style_based_message' => false,

// Keep the additional empty lines below this for safeties sake

// Callback function for sending the message
// Creation callback snippet for defining site-specific function
'create_callback_snippet' => null,

// Callback function will be called after creation e.g. to provide interface for page linking system
// $create_callback_function($controller, $config)
'create_callback_function' => null,
