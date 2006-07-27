<?php
$i18n =& $GLOBALS['midcom']->get_service('i18n');
$l10n = $i18n->get_l10n('midcom.admin.controls.l10n');
$data = $l10n->get('perm-denied text');
?>
<div class='processing_message'>&(data:h);</div>