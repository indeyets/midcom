<?php
// $data =& $_MIDCOM->get_custom_context_data('request_data');
?>
        <li id="net_nemein_personnel_group_<?php echo $data['index']; ?>">
            <input type="hidden" name="sortable[]" value="group::group_<?php echo $data['group']->guid; ?>::<?php echo $data['group']->official; ?>" />
            <p class="unsorted"><?php echo $data['l10n']->get('unsorted'); ?></p>
            <ul id="net_nemein_personnel_group_list_<?php echo $data['index']; ?>" class="section">
