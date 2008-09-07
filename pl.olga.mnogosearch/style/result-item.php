<?php
$doc = $data['doc'];
?>
  <DT><b>&(doc['ndoc']);.</b> <a href="&(doc['url']);"><b>&(doc['title']:h);</b></a> [<b>&(doc['rating']);</b>]</dt>
  <DD><small><p>&(doc['text']:h);...</p>
    <UL>
      <li><A HREF="&(doc['url']);">&(doc['url']);</A>  &(doc['lastmod']);, &(doc['docsize']); <?php echo $data['l10n']->get('bytes')?></li>
&(doc['clonestr']:h);
    </UL></small>
  </dd>
