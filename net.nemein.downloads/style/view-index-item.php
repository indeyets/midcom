<?php
global $view;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>


<li><a href="&(prefix);&(view["name"]);.html">&(view["title"]);</a></li>