<?php
$data['view_imgurl_always'] = MIDCOM_STATIC_URL . '/stock-icons/16x16/stock_mark.png';
$data['view_imgurl_never'] = MIDCOM_STATIC_URL . '/stock-icons/16x16/cancel.png';
?>
<div class="calendar-modal-window-content">
    <h1>Edit your public profile</h1>
    <div onclick="close_modal_window();">Close</div>
    
    <form id="org_maemo_calendar" name="org_maemo_calendar" action="/ajax/profile/publish/" method="post" enctype="multipart/form-data">
    <table cellspacing='0' cellpadding='0' border='0' style='border-collapse: collapse;'>