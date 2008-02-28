<?php
$view = $data['datamanager']->get_content_html();

if (isset($data['datamanager']->types['photo']->attachments_info['thumbnail']))
{
    $thumbnail = $data['datamanager']->types['photo']->attachments_info['thumbnail'];
}
else
{
    $thumbnail = array
    (
        'url' => '',
        'size_line' => '',
        'filename' => '',
    );
}

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
        <tr id="org_routamc_photostream_moderate_item_<?php echo $data['photo']->guid; ?>">
            <td class="thumbnail">
                <a href="&(prefix);moderate/<?php echo $data['photo']->guid; ?>"><img src="&(thumbnail['url']:h);" &(thumbnail['size_line']:h); alt="&(thumbnail['filename']:h);" /></a>
            </td>
            <td class="photographer">
                <?php echo $data['photographer']->name; ?>
            </td>
            <td class="details">
                <h3>&(view['title']:h);</h3>
                &(view['description']:h);
            </td>
            <td class="buttons">
                <form method="post" action="&(prefix);moderate/">
                    <p>
                        <input type="hidden" name="guid" value="<?php echo $data['photo']->guid; ?>" />
<?php
foreach ($data['buttons'] as $button)
{
    echo "<input type=\"submit\" name=\"f_{$button}\" value=\"{$data['l10n']->get($button)}\" class=\"{$button}\" />\n";
}
?>
                    </p>
                </form>
            </td>
        </tr>
