<?php
?>

<div class="calendar-modal-window-content">
    <h1>Create event</h1>
    <div onclick="close_modal_window();">Close</div>
    <div onclick="window.location='/event/create/<?php echo $data['selected_day']; ?>'"> Edit details</div>

    <div class="event-form">

        <?php 
        $data['controller']->display_form(); 
        ?>

    </div>
</div>