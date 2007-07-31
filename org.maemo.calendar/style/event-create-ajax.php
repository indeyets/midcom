<?php
?>

<div class="calendar-modal-window-content">
    <h1>Create event</h1>
    <div onclick="close_modal_window();">Close</div>
    <?php
    if ($data['full_schema_in_use'])
    {
    ?>
    <div onclick="load_modal_window('ajax/event/create/<?php echo $data['selected_day']; ?>?full_schema=0');">Basic create</div>
    <?php
    }
    else
    {
    ?>
    <div onclick="load_modal_window('ajax/event/create/<?php echo $data['selected_day']; ?>?full_schema=1');">Edit details</div>
    <?php
    }
    ?>

    <div class="event-form">

        <?php 
        $data['controller']->display_form(); 
        ?>

    </div>
</div>