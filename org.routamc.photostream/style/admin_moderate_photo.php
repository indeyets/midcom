<?php
$view = $data['datamanager']->get_content_html();
?>
<h1>&(view['title']:h);</h1>
&(view['photo']:h);
&(view['description']:h);

<form method="post" action="&(_MIDGARD['uri']);">
    <div class="form">
        <input type="hidden" name="guid" value="<?php echo $data['photo']->guid; ?>" />
<?php
foreach ($data['buttons'] as $button)
{
    echo "        <input type=\"submit\" name=\"f_{$button}\" value=\"{$data['l10n']->get($button)}\" class=\"{$button}\" />\n";
}
?>
    </div>
</form>