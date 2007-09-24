<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<p class="net_nemein_teams_quit">
<?php echo $data['l10n']->get('really quit?'); ?>
</p>
<p>
<p>
  <form method="post">
    <input type="submit" name="confirm_quit" value="<?php echo $data['l10n']->get('confirm'); ?>"/>
    <input type="submit" name="cancel" value="<?php echo $data['l10n']->get('cancel'); ?>"/>
  </form>
</p>

