<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="main">
    <?php
    if ($data['delete_succeeded'])
    {
        echo $data['l10n']->get('event deleted');
    }
    else
    {
        ?>
        <form method="post" class="datamanager" action="<?php echo $_MIDGARD['uri']; ?>">
            <fieldset class="area">
                <legend><?php echo $data['event']->title; ?></legend>
                <label for="org_openpsa_calendar_deleteok">
                    <?php echo $data['l10n']->get('really delete event'); ?>
                    <input type="submit" id="org_openpsa_calendar_deleteok" name="org_openpsa_calendar_deleteok" value="<?php echo $data['l10n_midcom']->get('yes'); ?>" />
                </label>
            </fieldset>
        </form>
        <?php
    }
    ?>
</div>