<?php
$view = $data['controller']->get_content_html();
?>

<div class="calendar-modal-window-content">
    <h1>Show event</h1>
    <div onclick="close_modal_window();">Close</div>
    <div onclick="window.location='/event/edit/<?php echo $data['event']->guid; ?>'">Edit</div>

    <div class="event-form">

        <?php 
        $data['controller']->display_view();
        ?>

    </div>
</div>