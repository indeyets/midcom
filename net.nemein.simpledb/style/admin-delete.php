<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$dn_data= $data['datamanager']->get_array();
?>

<h1><?php $data['view_title']; ?></h1>

<form method="post" class="datamanager" action="<?php echo $_MIDGARD['uri']; ?>">
    <fieldset class="area">
        <legend><?php echo $data['entry']->title; ?></legend>
        <label for="net_nemein_simpledb_deleteok">
            <?php echo $data['l10n']->get('really delete entry'); ?>
            <input type="submit" id="net_nemein_simpledb_deleteok" name="net_nemein_simpledb_deleteok" value="<?php echo $data['l10n_midcom']->get('yes'); ?>" />
        </label>
    </fieldset>
</form>
