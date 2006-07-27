<?php
global $view_item, $view_item_date;
?>
<p class="newsitem"><a href="&(view_item["link"]);" class="news">&(view_item["title"]);</a>
<?php
if ($view_item_date) {
  $date = strftime("%x",$view_item_date);
  echo "(".$date.")";
}
?></p>