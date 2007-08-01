<?php
?>
<div class="calendar-modal-window-content">
    <h1>Show event</h1>
    <div onclick="close_modal_window();">Close</div>
    
    <div class="event-form">
    
        <?php
        if (isset($data['deleted']))
        {
            echo $data['l10n']->get('event deleted');
        ?>
        <script type="text/javascript">
        on_event_deleted('<?php echo $data['deleted'];?>');
        </script>
        <?php
        }
        else
        {
        ?>
        <form name="event-delete-form" id="event-delete-form">
            <fieldset class="area">
                <legend><?php echo $data['event']->title; ?></legend>
                <?php echo $data['l10n']->get('really delete event'); ?>
                <label for="org_maemo_calendar_event_deleteok">
                    <input type="submit" id="org_maemo_calendar_event_deleteok" name="org_maemo_calendar_event_deleteok" value="<?php echo $data['l10n_midcom']->get('yes'); ?>" />
                </label>
                <label for="org_maemo_calendar_event_deletecancel">
                    <input type="button" onclick="close_modal_window();" id="org_maemo_calendar_event_deletecancel" name="org_maemo_calendar_event_deletecancel" value="<?php echo $data['l10n_midcom']->get('no'); ?>" />
                </label>
            </fieldset>
        </form>
        <script type="text/javascript">
        enable_event_delete_form('<?php echo $data['event']->guid;?>');
        </script>
        <?php
        }
        ?>

    </div>
</div>