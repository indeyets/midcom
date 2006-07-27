<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>


<h2><?php echo $view['l10n']->get('create article'); ?></h2>

<?php if (count($view['schemadb_index']) < 1) { ?>
  <p><?php echo $view['l10n']->get('no schemas available'); ?></p>
<?php } else { ?>
  <ul>
<?php 
    foreach ($view['schemadb_index'] as $name => $desc) 
    { 
        $text = sprintf($view['l10n_midcom']->get('create %s'), $desc);
?>
    <li><a href="&(prefix:s);create/&(name:s);.html">&(text);</a></li>
<?php } ?>
  </ul>
<?php } ?>