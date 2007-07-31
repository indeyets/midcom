<?php
?>

<div class="calendar-modal-window-content">
    <h1>Show event</h1>
    <div onclick="close_modal_window();">Close</div>
    <div onclick="load_modal_window('ajax/event/edit/<?php echo $data['event']->guid; ?>');">Edit</div>

    <div class="event-form">

        <?php 
        $data['controller']->display_view();
        ?>

    </div>
</div>