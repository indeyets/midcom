<?php global $view; 
   global $view_descriptions;
   global $view_id;
   global $midcom; 
   global $view_title;
   $prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX); ?>

<dl>

<?php foreach ($view_descriptions as $name => $description) { ?>

<dt>&(description);</dt>
<dd><pre><?echo htmlentities(wordwrap($view[$name],60));?></pre></dd>

<?php } ?>

</dl>