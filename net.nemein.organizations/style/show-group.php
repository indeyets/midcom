<?php
$view = $data['datamanager']->get_content_html();
?>
<div class="vcard">
    <h1 class="fn org organization-name">&(view['official']:h);</h1>

    <address>&(view['location']:h);</address>

    <?php 
    if ($view['email']) 
    { 
        echo "<p><a href=\"mailto:{$view['email']}\">{$view['email']}</a></p>\n";
    }
    ?>

    <div class="description">
        &(view["description"]:h);
    </div>
</div>