<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
?>
<div class="area">
    <h2><?php echo $view_data['l10n']->get("user account"); ?></h2>
    <?php
    if ($view_data['person']->username)
    {
        echo "<p>{$view_data['person']->username}</p>";
    }
    else
    {
        echo '<p><span class="metadata">'.$view_data['l10n']->get("no account").'</span></p>';
    }
    ?>
</div>