<?php
$_MIDCOM->auth->require_admin_user();

$ret = fi_hut_loginbroker_viewer::load_callback_class('fi_hut_loginbroker_callbacks_createperson');
$ret = fi_hut_loginbroker_viewer::load_callback_class('fi_hut_loginbroker_callbacks_updateperson');
$ret = fi_hut_loginbroker_viewer::load_callback_class('fi_hut_loginbroker_callbacks_resetpasswd');

/*
$handler = new fi_hut_loginbroker_callbacks_resetpasswd();
echo "<pre>" . htmlspecialchars($handler->generate_password()) . "</pre>\n";
$data = array();
$ret = $handler->reset_passwd('rambo', $data, 1);
echo "\$handler->reset_passwd('rambo', \$data, 1) returned: {$ret}, data:<pre>\n";
print_r($data);
echo "</pre>\n";
$ret = $handler->rollback();
echo "\$handler->rollback() returned: {$ret}<br/>";
*/

/*
$data = array();
$handler = new fi_hut_loginbroker_callbacks_createperson();
$data['property_map'] = array
(
    'firstname' => 'rambo',
    'lastname' => 'foo',
    'email' => 'test@nemein.com',
);
$ret = $handler->create('luide', $data, 1);
echo "\$handler->create('luide', \$data, 1) returned: {$ret}, data:<pre>\n";
print_r($data);
echo "</pre>\n";
$ret = $handler->rollback();
echo "\$handler->rollback() returned: {$ret}<br/>";
*/

/*
$data = array();
$handler = new fi_hut_loginbroker_callbacks_updateperson();
$data['property_map'] = array
(
    'firstname' => 'rambo',
    'lastname' => 'foo',
    'email' => 'test@nemein.com',
);
$ret = $handler->update('rambo', $data, 1);
echo "\$handler->update('rambo', \$data, 1) returned: {$ret}, data:<pre>\n";
print_r($data);
echo "</pre>\n";
$ret = $handler->rollback();
echo "\$handler->rollback() returned: {$ret}<br/>";
*/

?>