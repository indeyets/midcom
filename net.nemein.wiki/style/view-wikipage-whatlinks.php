<?php
global $view;
global $view_whatlinks;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h1><?php echo sprintf($GLOBALS["view_l10n"]->get("pages linking to %s"),$view["title"]); ?></h1>

<?php if (count($view_whatlinks) > 0) { ?>
  <ul>
  <?php
  foreach ($view_whatlinks as $title => $uri) {
    ?>
    <li><a href="&(prefix);&(uri);">&(title);</a></li>
    <?php
  }
  ?>
  </ul>
<?php } else { ?>
  <p><?php echo $GLOBALS["view_l10n"]->get("no links to page"); ?></p>
<?php } ?>