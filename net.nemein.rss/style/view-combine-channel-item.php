<?php
global $view_item, $view_item_date;
?>
<p class="newsitem">
<a href="&(view_item["link"]);" class="news">&(view_item["title"]);</a>
<?php
if ($view_item_date) {
  $date = strftime("%x",$view_item_date);
  echo "(".$date.")";
}

// Show originating feed
echo "<br />in ".$view_item["channel"]["title"];
?>
 <a href="<?php echo $view_item["channel"]["link"]; ?>" class="news">&raquo;</a>
</p>