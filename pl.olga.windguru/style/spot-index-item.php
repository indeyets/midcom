<?php
// Available request keys: datamanager, article, view_url, article_counter
$view = $data['spot'];
$spot = $data['article'];
?>

<div style="clear: left;">
    <h2 class="entry-title"><a href="&(data['prefix']);&(spot.name);.html" rel="bookmark">&(view['title']:h);</a></h2>
<?php
    
    if (isset($view['comment']))
    {
        ?>
        <p class="entry-summary">&(view['comment']:h);</p>
        <?php
    }
    
?>
</div>