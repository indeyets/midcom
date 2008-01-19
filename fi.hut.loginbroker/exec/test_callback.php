<?php
$_MIDCOM->auth->require_admin_user();
$classes = array
(
    'fi_hut_loginbroker_callbacks_createperson',
    'fi_hut_loginbroker_callbacks_updateperson',
    'fi_hut_loginbroker_callbacks_resetpasswd',
    'fi_hut_loginbroker_callbacks_affiliations',
);

echo "Loading callback classes...<br>\n";
foreach ($classes as $class)
{
    $ret = fi_hut_loginbroker_viewer::load_callback_class($class);
    echo "&nbsp;&nbsp;&nbsp;<tt>{$class}</tt>: {$ret}<br>\n";
}
echo "Done.<br>\n";


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

/*
$_SERVER['HTTP_SHIB_EPA'] = 'member;trustee';
//$_SERVER['HTTP_SHIB_EPA'] = 'trustee';
//$_SERVER['HTTP_SHIB_EPA'] = 'member';
//$_SERVER['HTTP_SHIB_EPA'] = '';
$data = array();
$handler = new fi_hut_loginbroker_callbacks_affiliations();
$data['property_map'] = array
(
    'firstname' => 'luidefname',
    'lastname' => 'luidelname',
    'email' => 'test@nemein.com',
);
$ret = $handler->update('luide', $data, 1);
echo "\$handler->update('luide', \$data, 1) returned: {$ret}, local_data:<pre>\n";
print_r($handler->_local_data);
echo "</pre>\n";
$ret = $handler->rollback();
echo "\$handler->rollback() returned: {$ret}<br/>";
*/
/*
$ret = $handler->create('luide', $data, 1);
echo "\$handler->create('luide', \$data, 1) returned: {$ret}, local_data:<pre>\n";
print_r($handler->_local_data);
echo "</pre>\n";
$ret = $handler->rollback();
echo "\$handler->rollback() returned: {$ret}<br/>";
*/

?>