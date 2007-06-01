<?php
$_MIDCOM->auth->require_admin_user();
if (!isset($_REQUEST['photo'])
    || !mgd_is_guid($_REQUEST['photo']))
{
    echo "set 'photo' to GUID as GET parameter<br>\n";
    return;
}

$photo = new org_routamc_photostream_photo_dba($_REQUEST['photo']);
$photo->read_exif_data(true);
echo "EXIF for '{$photo->title}'<pre>\n";
print_r($photo->raw_exif);
echo "</pre>\n";
//$photo->update();
?>