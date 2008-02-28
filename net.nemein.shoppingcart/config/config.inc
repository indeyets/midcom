/**
 * Which backend handler to use for checkout
 * Currently supported values: 'null' and 'email'
 */
'checkout_backend' => 'null',
/**
 * Where to send the orders processed with the email backend
 */
'backend_email_sendto' => '',
'backend_email_subject' => 'product order from website',
/**
 * Schemas used by the checkout processors
 */
'schemadb' => 'file:/net/nemein/shoppingcart/config/schemadb_default.inc',
