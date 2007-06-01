<?php
// $data =& $_MIDCOM->get_custom_context_data('request_data');
?>
                 <li class="sortable" title="<?php echo $data['l10n']->get('drag and drop to sort'); ?>"><input type="hidden" name="sortable[]" value="person::membership_&(data['membership_guid']:h);::person_<?php echo $data['person']->guid; ?>" /><?php echo $data['person']->rname; ?></li>
