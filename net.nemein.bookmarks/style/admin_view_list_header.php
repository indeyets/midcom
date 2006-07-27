<?php

if ($view_tag == "")
{?>
    <h1>Bookmarks</h1>
<?php
} else {?>
    <h1>Bookmarks by tag "&(view_tag);"</h1>
<?php
}

$i18n =& $GLOBALS["midcom"]->get_service("i18n");
$l10n =& $i18n->get_l10n("net.nemein.bookmarks");
$l10n_midcom =& $i18n->get_l10n("midcom");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>