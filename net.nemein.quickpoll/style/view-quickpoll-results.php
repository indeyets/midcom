<?php
global $view;
global $view_options;
global $view_votes;
global $view_total_votes;
?>
<h1>&(view.title);</h1>

<?php
foreach ($view_options as $key => $option) {
  $votes = $view_votes[$key];
  if ($view_total_votes == 0 || $votes == 0)
  {
      $percentage = 0;
  } 
  else
  {
      $percentage = round(100 / $view_total_votes * $votes);
  }
  ?>
  <div class="quickpoll_option">
     &(option);: &(votes); (&(percentage);%)
  </div>
  <?php
}
?>

<p>
<?php echo sprintf($GLOBALS["view_l10n"]->get("%d total votes"),$view_total_votes); ?>
</p>
