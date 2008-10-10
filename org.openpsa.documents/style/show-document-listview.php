<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['document_dm'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());

// MIME type
$icon = MIDCOM_STATIC_URL . '/stock-icons/mime/gnome-text-blank.png';
if ($view['document'])
{
    $icon = midcom_helper_get_mime_icon($view['document']['mimetype']);
}

?>
<div style="float: left; height: 95px; width: 80px; overflow: hidden; margin: 7px; text-align: center;"><?php
?>

<a style="text-decoration: none;" href="&(node[MIDCOM_NAV_FULLURL]);document/<?php echo $data['document']->guid; ?>/">
    <?php
    if ($icon)
    {
        ?>
        <div class="icon"><img src="&(icon);" <?php
        if ($view['document'])
        {
            echo 'title="'.sprintf($data['l10n']->get("%s %s document"), midcom_helper_filesize_to_string($view['document']['filesize']), $data['l10n']->get($view['document']['mimetype'])).'" ';
        }
        ?>style="border: 0px;"/></div>
        <?php
    } ?>
    &(view['title']);
</a>
</div>