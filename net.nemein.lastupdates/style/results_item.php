<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$document =& $data['document'];
if (!empty($document->editor->name))
{
    $modified_loc = sprintf($data['l10n']->get('updated on %s by %s'), strftime('%c', $document->edited), $document->editor->name);
}
else
{
    $modified_loc = sprintf($data['l10n']->get('updated on %s'), strftime('%c', $document->edited));
}
?>
    <div class="net_nemein_lastupdates_result">
      <h3><a href='&(document.document_url);'>&(document.title);</a></h3>
      <div class="net_nemein_lastupdates_result_metadata">
        <p class="modified">&(modified_loc);</p>
        <div class="abstract">
            &(document.abstract:h);
        </div>
      </div>
    </div>
