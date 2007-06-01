<?php
// Available request keys: article, datamanager

// $data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();
?>

<h1>&(view['firstname']:h); &(view['lastname']:h);</h1>

<?php if ($view['image']) { ?>
    <div style="float: right; padding: 5px;">&(view['image']:h);</div>
<?php } ?>

<?php if ($view["title"]) { ?>
  <p><b>&(view["title"]);</b></p>
<?php } ?>

<?php if ($view["workphone"]) { ?>
  <p>Phone: &(view["workphone"]);</p>
<?php } ?>

<?php if ($view["email"]) { ?>
  <p>Email: <a href="mailto:&(view["email"]);">&(view["email"]);</a></p>
<?php } ?>

&(view["description"]:h);