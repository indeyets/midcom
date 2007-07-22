<?php
?>

<div class="calendar-modal-window-content">
    <h1>Edit event</h1>
    <div onclick="close_modal_window();">Close</div>

    <div class="event-form">

        <?php 
        $data['controller']->display_form(); 
        ?>

    </div>
</div>