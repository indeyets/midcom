<?php
global $view_title;
global $view_layouts;
global $midcom;
global $view_topic;

$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $GLOBALS["view_l10n"]->get("create news article"); ?></h2>

<?php if ($_MIDCOM->auth->can_do('midgard:create', $view_topic)) { ?>

<?php if (count($view_layouts) < 1) { ?>
  <p><?php echo $GLOBALS["view_l10n"]->get("no schemas available"); ?></p>
<?php } else { ?>
  <ul>
<?php 
    foreach ($view_layouts as $name => $desc) 
    { 
        $text = sprintf($GLOBALS['view_l10n_midcom']->get('create %s'), $desc);
?>
    <li><a href="&(prefix:s);create/&(name:s);.html">&(text);</a></li>
<?php } ?>
  </ul>
<?php } ?>

<?php } else { ?>
  <p><?php echo $GLOBALS["view_l10n"]->get('not enough privileges to create articles'); ?></p>
<?php } ?>
