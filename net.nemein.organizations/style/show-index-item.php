<?php
$view = $data['datamanager']->get_content_html();
?>
<li class="vcard">
    <h2 class="fn org organization-name"><a href="&(data['view_url']);" class="url">&(view['official']:h);</a></h2>
    <address>
        &(view['location']:h);
    </address>
</li>