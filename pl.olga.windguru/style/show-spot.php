<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls
$view = $data['view_article'];
$dm = $data['datamanager'];

$spotid = $dm->types['spotid']->value;
$key = $dm->types['key']->value;

$wg = new WindguruFcst($spotid,$key,$data['config']->get('lang'),$data['config']);
$forecast = $wg->show();


?>

<h1>&(view['title']:h);</h1>
<div style="display:none">&(view['spotid']:h); &(view['key']:h);</div>
&(view['comment']:h);

<div id="pl_olga_windguru_forecast">
&(forecast:h);
</div>