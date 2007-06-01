<?php
// $data =& $_MIDCOM->get_custom_context_data('request_data');
$item = $data['item'];
?>
    <url>
        <loc><?php echo $item[MIDCOM_NAV_FULLURL]; ?></loc>
<?php
if (   array_key_exists(MIDCOM_META_EDITED, $item)
    && $item[MIDCOM_META_EDITED])
{
    $tz = strftime('%z',$item[MIDCOM_META_EDITED]);
    ereg ("([+-])([0-9]{2})([0-9]{2})",$tz,$atoms);
    $tz = $atoms[1].$atoms[2].":".$atoms[3];

    echo "        <lastmod>".strftime('%Y-%m-%dT%H:%M:%S', $item[MIDCOM_META_EDITED]).$tz."</lastmod>\n";
}
?>
    </url>
