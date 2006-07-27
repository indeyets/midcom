<?php
global $view, $view_name, $view_openpsa;
$image = $view["logo"];
$image_thumbnail = $image["thumbnail"];

if ($image) { ?>
  <a href="&(image["url"]);"><img src="&(image_thumbnail["url"]);" align="right" title="&(image["description"]);" alt="&(image["description"]);" &(image_thumbnail["size_line"]:h);></a>
<?php } ?>

<h1>&(view_name);</h1>

<?php if ($view["homepage"]) { ?>
  <p>Web site: <a href="&(view["homepage"]);">&(view["homepage"]);</a></p>
<?php } ?>

&(view["description"]:f);