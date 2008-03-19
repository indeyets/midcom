<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo $data['l10n']->get('create website'); ?></h1>
  
 <?php
 if (isset($data['report']))
 {
     echo "<fieldset>";
     echo "<h2>" . $data['l10n']->get('report') . "</h2>";
     echo "<p>";
     echo "Website title: " . $data['report']['sitename'] . "<br/>";
     echo "Host name: " . $data['report']['hostname'] . "<br/>";
     echo "Host port: " . $data['report']['port'] . "<br/>";  
     echo "Root topic: " . $data['report']['topicname'] . "<br/>";
     echo "</p>";
     echo "<p>";
     echo "<a href=\"http://" . $data['report']['hostname'] . $data['report']['prefix'] . "\">http://" 
        . $data['report']['hostname'] . $data['report']['prefix'] . "</a>";
     echo "</p>";
     echo "<p>";
     echo "<b>" . $data['l10n']->get('generated vhost') . ": </b>" . $data['report']['vhost_filename'] . "<br/>";
     echo "<b>" . $data['l10n']->get('notification has been sent to') . ": </b>" . $data['report']['notification_email'];
     echo "</p>";
     echo "</fieldset>";
     echo "<a href=\"" . $prefix . "\">" . $data['l10n']->get('run sitewizard again') . "</a>";
 }
 else
 {
     echo "<fieldset>";
     echo "<h2>" . $data['l10n']->get('You are about to create a website') . "</h2>";
     echo "<p>";    
     echo "Website title: " . $data['prereport']['sitename'] . "<br/>";
     echo "Host name: " . $data['prereport']['hostname'] . "<br/>";
     echo "Host port: " . $data['prereport']['port'] . "<br/>";  
     echo "Root topic: " . $data['prereport']['topicname'] . "<br/>";
     echo "</p>";
     echo "<p>";
     echo "<b>" . $data['l10n']->get('vhost will be generated') . ": </b>" . $data['prereport']['vhost_filename'] . "<br/>";
     echo "<b>" . $data['l10n']->get('notification will be sent to') . ": </b>" . $data['prereport']['notification_email'];
     echo "</p>";
     echo "</fieldset>";
 ?>
        
<form method="post" name="tkk_sitewizard_website">        
 <input type="submit" name="tkk_sitewizard_website_submit" value="<?php echo $data['l10n']->get('create'); ?>" />
 <input type="submit" name="tkk_sitewizard_website_cancel" value="<?php echo $data['l10n']->get('cancel'); ?>" />
</form>   

<?php
 }
?>