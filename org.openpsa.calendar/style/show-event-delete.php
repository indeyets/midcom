<?php
$view =& $GLOBALS['midcom']->get_custom_context_data('request_data');
?>
<div class="main">
    <?php 
    if ($view['delete_succeeded'])
    {
        echo $view['l10n']->get('event deleted');
    }
    else
    {
        ?>
        <form method="post" class="datamanager" action="<?php echo $_MIDGARD['uri']; ?>">
            <fieldset class="area">
                <legend><?php echo $view['event']->title; ?></legend>
                <label for="org_openpsa_calendar_deleteok">
                    <?php echo $view['l10n']->get('really delete event'); ?>
                    <input type="submit" id="org_openpsa_calendar_deleteok" name="org_openpsa_calendar_deleteok" value="<?php echo $view['l10n_midcom']->get('yes'); ?>" />
                </label>
            </fieldset>
        </form>
        <?php
    }
    ?>
</div>
