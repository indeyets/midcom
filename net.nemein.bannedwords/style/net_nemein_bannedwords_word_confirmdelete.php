<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$delete_guid = $data['delete_guid'];

$banned = new net_nemein_bannedwords_word_dba();
$banned->get_by_guid($delete_guid);

?>

<h2><?php echo $_MIDCOM->i18n->get_string('delete', 'net.nemein.bannedwords'); ?> &(banned.bannedWord);?</h2>

<form method="post" action="<?php echo $_MIDGARD['prefix']; ?>/__mfa/net.nemein.bannedwords/delete/&(delete_guid);.html">
  <input class="save" accesskey="s" name="net_nemein_bannedwords_word_delete" value="
      <?php echo $_MIDCOM->i18n->get_string('delete', 'net.nemein.bannedwords'); ?>" type="submit" />
  <input class="cancel" accesskey="c" name="net_nemein_bannedwords_word_cancel" value="
      <?php echo $_MIDCOM->i18n->get_string('cancel', 'net.nemein.bannedwords'); ?>" type="submit" />
</form>
