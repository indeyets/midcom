<?php
global $midcom; 
global $view;
global $view_id;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h3><?php echo $GLOBALS["view_l10n"]->get("delete article"); ?>: <?php echo htmlentities($view["title"]); ?></h3>

    <form action="&(prefix);/delete/&(view_id);.html" method="POST">
        <input type="submit" name="pl_olga_files_deleteok" 
          value="<?php echo $GLOBALS["view_l10n_midcom"]->get("delete"); ?>">
        <input type="submit" name="pl_olga_files_deletecancel" 
          value="<?php echo $GLOBALS["view_l10n_midcom"]->get("cancel"); ?>">
    </form>

