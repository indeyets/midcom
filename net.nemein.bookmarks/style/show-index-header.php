<?php
global $view_topic;
?>
<h1>&(view_topic->extra);</h1>
<?php
$i18n =& $_MIDCOM->get_service("i18n");
$l10n =& $i18n->get_l10n("net.nemein.bookmarks");
$l10n_midcom =& $i18n->get_l10n("midcom");
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="net_nemein_bookmarks_list">