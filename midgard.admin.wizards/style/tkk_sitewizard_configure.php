<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('configure structure'); ?></h1>

<?php
   $data['nullstorage_controller']->display_form();
?>


<br/><br/>
<!--
<form method="post" name="tkk_sitewizard_style">        
<input type="submit" name="tkk_sitewizard_configure_submit" value="<?php echo $data['l10n']->get('next'); ?>">
</form> 
-->
