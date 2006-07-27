<?php
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $request_data['wikipage_view'];
?>

<h1>&(view['title']:h);</h1>

<form method="post" class="datamanager" action="<?php echo $_MIDGARD['uri']; ?>">
    <label for="net_nemein_wiki_deleteok">
        <span class="field_text"><?php echo $request_data['l10n']->get('really delete page'); ?></span>
        <input type="submit" id="net_nemein_wiki_deleteok" name="net_nemein_wiki_deleteok" value="<?php echo $request_data['l10n_midcom']->get('yes'); ?>" />
    </label>
</form>
<?php

if ($view['content'] != '')
{
    ?>
    &(view["content"]:h);
    <?php
} 
else
{
    echo "<p class=\"stub\">".$GLOBALS['request_data']['l10n']->get('this page is stub')."</p>";
}
?>