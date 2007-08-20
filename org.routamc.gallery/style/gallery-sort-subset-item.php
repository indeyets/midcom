<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_photo'];
$thumbnail = $data['datamanager']->types['photo']->attachments_info['thumbnail'];
?>
                <li class="sortable" id="org_routamc_gallery_photo_<?php echo $data['photo']->id; ?>">
                    <span class="thumbnail"><img src="&(thumbnail['url']:h);" &(thumbnail['size_line']:h); alt="&(thumbnail['filename']:h);" /></span>
                    <div class="details">
                        <p class="title">&(view['title']:h);</p>
                        <p class="description">&(view['description']:h);</p>
                    </div>
                    <input type="hidden" name="sortable[]" value="link_<?php echo "{$data['link_id']}_{$data['photo']->id}" ?>" />
                    <hr class="break" />
                </li>            
