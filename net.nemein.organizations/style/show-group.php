<?php
// Available request keys: article, datamanager

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();
?>

<h1>&(view['official']:h);</h1>

<?php if ($view["email"]) { ?>
  <p>Email: <a href="mailto:&(view["email"]);">&(view["email"]);</a></p>
<?php } ?>

&(view["description"]:h);