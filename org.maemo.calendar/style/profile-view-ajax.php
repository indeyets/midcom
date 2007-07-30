<?php
?>

<div class="calendar-modal-window-content">
    <h1>View profile</h1>
    <div onclick="close_modal_window();">Close</div>
        
    <?php 
    $data['controller']->display_view();
    ?>    
    
</div>