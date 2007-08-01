<?php
?>
<div class="calendar-modal-window-content">
    <h1>Edit your profile</h1>
    <div onclick="close_modal_window();">Close</div>
    
    <?php
    if ($data['saved'])
    {
        $_MIDCOM->uimessages->add($data['l10n']->get('org.maemo.calendar'), $data['l10n']->get('profile saved successfully'), 'ok');
        $_MIDCOM->uimessages->show();
    ?>
    <script type="text/javascript">
        load_modal_window("ajax/profile/view");
    </script>
    <?php
    }
    else
    {
        $data['controller']->display_form();
    ?>    
    <script type="text/javascript">
        takeover_dm2_form({
            oncancel: function(){load_modal_window('ajax/profile/view');return false;}
        });
    </script>
    <?php
    }
    ?>    
</div>