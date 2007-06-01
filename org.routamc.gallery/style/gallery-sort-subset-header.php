<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
        <li class="&(data['class']);" id="org_routamc_gallery_<?php echo $data['gallery']->id; ?>_container">
            <h3 class="handle" ondblclick="javascript:alter_title(this, 'group');">
                <span class="gallery-name"><?php echo $data['gallery']->extra; ?></span>
                <input type="hidden" name="sortable[]" value="group_<?php echo $data['gallery']->id; ?>_<?php echo $data['gallery']->extra; ?>" />
            </h3>
            <ul class="section" id="org_routamc_gallery_<?php echo $data['gallery']->id; ?>_photos">
