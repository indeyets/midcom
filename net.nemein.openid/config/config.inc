/* OpenID settings */
'autoregistration_enable' => true,
'autoregistration_group' => '',

/* URL to default identity provider in OpenID help texts. Change this to reflect affiliate IDs etc */
'identity_provider_url' => 'https://www.myopenid.com/affiliate_signup?affiliate_id=17',

/* The schema database to use for registrations */
'schemadb' => 'file:/net/nehmer/account/config/schemadb_account_creation.inc',
/* The schema in the database to use for registrations. If available, autoregisted users will be added to group matching this name */
'schema' => 'account',