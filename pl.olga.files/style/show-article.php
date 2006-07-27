<?php
global $view;
$image = $view["image"];
?>

<h1>&(view["title"]);</h1>

<?php if ($image) { ?><img src="&(image["url"]);" align="right" title="&(image["description"]);" alt="&(image["description"]);" &(image["size_line"]:h);><?php } ?>

&(view["content"]:h);
