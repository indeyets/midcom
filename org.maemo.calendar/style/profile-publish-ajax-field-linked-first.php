<?php
$field =& $data['current_field'];
?>
<tr>
    <td style='border-top: 1px dashed black; padding-right: 1em;' nowrap='nowrap'>&(field['title']:h);</td>
    <td style='border-top: 1px dashed black; padding-right: 1em;'>&(field['content']:h);</td>
    <td style='border-top: 1px dashed black;' rowspan='&(data['total_fields']);'><?php midcom_show_style('profile-publish-field-checkbox'); ?></td>
</tr>