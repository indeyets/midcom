<?php

$is_group = false;

if (! empty($_GET['group']))
{
    $is_group = true;
    $guid = $_GET['group'];
}
else
{
    $guid = $_GET['person'];
}


$priv = new midcom_core_privilege_db();
$priv->objectguid = $guid;
$priv->name = 'midcom:ajax';
if ($is_group)
{
    $priv->assignee = "grp:{$guid}";
}
else
{
    $priv->assignee = "user:{$guid}";
}
$priv->classname = 'midcom_services_toolbars';
$priv->value = 1;
$priv->create();

?>