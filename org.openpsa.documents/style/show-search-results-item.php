<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['metadata_dm'];
$document_type = sprintf($data['l10n']->get("%s %s document"), midcom_helper_filesize_to_string($view['document']['filesize']), $data['l10n']->get($view['document']['mimetype']));
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
$score = round($data['metadata_search']->score * 100);

// MIME type
$icon = MIDCOM_STATIC_URL . '/stock-icons/mime/gnome-text-blank.png';
if ($view['document'])
{
    $icon = midcom_helper_get_mime_icon($view['document']['mimetype']);
}
?>
<dt style="clear: left;"><a href="&(node[MIDCOM_NAV_FULLURL]);document_metadata/<?php echo $data['metadata']->guid; ?>/"><?php echo $view['title']; ?></a></dt>
<dd>
<?php if ($icon)
{
    ?>
    <div class="icon" style="float: left; margin-right: 8px;"><a style="text-decoration: none;" href="&(node[MIDCOM_NAV_FULLURL]);document_metadata/<?php echo $data['metadata']->guid; ?>/"><img src="&(icon);" <?php
        if ($view['document'])
        {
            echo 'title="'.$document_type.'" ';
        }
    ?>style="border: 0px;"/></a></div>
    <?php
} ?>

<ul>
    <li><?php echo sprintf($data['l10n']->get('score: %d%%'), $score); ?></li>
    <li><?php echo $document_type; ?></li>
    <?php
    if ($data['metadata_search']->abstract != 0)
    {
        echo "<li>".$data['metadata_search']->abstract."</li>\n";
    }
    ?>
</ul>
</dd>