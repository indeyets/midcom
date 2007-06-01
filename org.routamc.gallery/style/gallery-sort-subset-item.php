<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();
$thumbnail = $data['datamanager']->types['photo']->attachments_info['thumbnail'];
?>
                <li class="sortable" id="org_routamc_gallery_photo_<?php echo $data['photo']->id; ?>">
                    <span class="thumbnail"><img src="&(thumbnail['url']:h);" &(thumbnail['size_line']:h); alt="&(thumbnail['filename']:h);" /></span>
                    <span class="title"><?php echo $data['photo']->title; ?></span>
                    <input type="hidden" name="sortable[]" value="link_<?php echo "{$data['link_id']}_{$data['photo']->id}" ?>" />
                </li>            
