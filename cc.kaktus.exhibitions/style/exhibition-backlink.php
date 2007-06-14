<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<hr class="backlink" />
<div class="backlink" id="cc_kaktus_exhibitions_subpages">
    <a href="&(prefix);&(data['event_url']:h);"><?php echo $data['l10n']->get('back to the exhibition page'); ?></a>
</div>