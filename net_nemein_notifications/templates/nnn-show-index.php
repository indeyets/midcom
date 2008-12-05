<?php
/**
 * @package net_nemein_notifications
 */
?>
<ul class="hfeed">
    <li class="hentry" tal:repeat="notification net_nemein_notifications/notifications">
        <abbr title="2007-08-01" tal:attributes="title midcomDateRfc: notification/metadata/published" tal:content="midcomDateShort: notification/metadata/published" class="published">2007-08-01</abbr>
        <a href="#" tal:content="notification/title" class="entry-title" rel="bookmark">Headline</a>
    </li>
</ul>