<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo $data['l10n']->get('no current exhibition'); ?></h1>
<?php
if ($data['event'])
{
    $view = $data['datamanager']->get_content_html();
    ?>
    <p>
        <?php echo $data['l10n']->get('we are at the moment building the next exhibition'); ?>:
    </p>
    <h2><a href="&(prefix:h);&(data['event_url']:h);">&(view['title']:h);</a></h2>
    <?php
    echo strftime('%x', $data['event']->start) . ' - ' . strftime('%x', $data['event']->end);
    ?>
    <?php
}
?>