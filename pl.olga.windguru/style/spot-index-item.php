<?php
// Available request keys: datamanager, article, view_url, article_counter
$view = $data['spot'];
$spot = $data['article'];

$dm = $data['datamanager'];

$spotid = $dm->types['spotid']->value;
$key = $dm->types['key']->value;

$wg = new WindguruFcst($spotid,$key,$data['config']->get('lang'),$data['config']);
$forecast = $wg->show();
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
    <div id="pl_olga_windguru_forecast">
    &(forecast:h);
    </div>
</div>