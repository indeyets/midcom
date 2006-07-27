<?php
global $view;
global $view_name;
global $view_date;

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

// Add posting date to abstract
if ($view["abstract"]) {
  $view["abstract"] = strftime("%x",$view_date)." -- ".$view["abstract"];
}
?>

<h1>&(view["title"]);</h1>

<?php
if ($view["image"]) { 
  $image = $view["image"];
  ?>
  <img src="&(image["url"]);" align="right" title="&(image["description"]);" alt="&(image["description"]);" &(image["size_line"]); />
<?php } ?>

<div class="abstract">
  &(view["abstract"]:f);
</div>

&(view["content"]:h);