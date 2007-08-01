<?php
?>
<div class="calendar-modal-window-content">
    <h1>Edit your profile</h1>
    <div onclick="close_modal_window();">Close</div>
    
    <?php
    $_MIDCOM->uimessages->add($data['l10n']->get('org.maemo.calendar'), $data['l10n']->get('profile publishing successfull'), 'ok');
    $_MIDCOM->uimessages->show();
    ?>
    <script type="text/javascript">
        close_modal_window();
    </script>
</div>