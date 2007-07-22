<?php
?>

<div class="standalone-window">
    <div class="standalone-window-content">

        <h1>View event</h1>
        <div onclick="history.back();">Close</div>

        <div class="content">

            <?php 
            $data['controller']->display_view(); 
            ?>

        </div>

    </div>
<div>