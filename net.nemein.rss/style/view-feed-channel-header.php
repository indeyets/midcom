<?php
global $view_channel;
?>
<h1>&(view_channel["title"]); <a href="&(view_channel["link"]);">&raquo;</a></h1>

<?php if (isset($view_channel["description"])) { ?>
  &(view_channel["description"]:f);
<?php } ?>