<?php
global $view;
global $view_link;
global $view_name;
$image = $view["logo"];
$image_thumbnail = $image["thumbnail"];
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<?php if ($image) { ?>
  <a href="&(prefix);&(view_link);.html"><img src="&(image_thumbnail["url"]);" align="center" title="&(image["description"]);" alt="&(image["description"]);" &(image_thumbnail["size_line"]:h);></a><br />
<?php } ?>
<dt><a href="&(prefix);&(view_link);.html">&(view_name);</a></dt>
<dd><ul>
<?php if ($view["homepage"]) { ?>
  <li>Web site: <a href="&(view["homepage"]);">&(view["homepage"]);</a></li>
<?php } ?>
</ul>
</dd>