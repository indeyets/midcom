<?php
global $view;
global $view_options;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>&(view.title);</h1>

<form method="post">
<?php
foreach ($view_options as $key => $option) {
  ?>
  <div class="quickpoll_option">
    <input type="radio" id="net_nemein_quickpoll_vote_&(key);" name="net_nemein_quickpoll_vote" value="&(key);" />
    <label for="net_nemein_quickpoll_vote_&(key);">&(option);</label>
  </div>
  <?php
}
?>
<input class="quickpoll_submit" type="submit" value="<?php echo $GLOBALS["view_l10n"]->get("vote"); ?>" />
</form>
