<?php
global $view_item, $view_item_date;
?>
<div class="rss-item">
  <?php if ($view_item["channel"]["icon"]) { ?>
    <a href="<?php echo $view_item["channel"]["link"]; ?>"
      ><img src="<?php echo $view_item["channel"]["icon"]; ?>" align="right" border="0" alt="<?php echo $view_item["channel"]["title"]; ?>" title="<?php echo $view_item["channel"]["title"]; ?>" 
    /></a>
  <?php } ?>
  <h2 class="rss-item-title"><a href="&(view_item["link"]);">&(view_item["title"]);</a></h2>

  <div class="rss-item-postinfo">
    <a href="<?php echo $view_item["channel"]["link"]; ?>"><?php echo $view_item["channel"]["title"]; ?></a>
    <?php if ($view_item_date) {
      $date = strftime("%x",$view_item_date);
      ?>
      - <span class="rss-item-date">&(date);</span>
      <?php
    } ?>
  </div>

  &(view_item["description"]:h);

</div>