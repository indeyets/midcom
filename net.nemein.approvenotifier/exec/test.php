<?php
$notifier = new net_nemein_approvenotifier(true);
$nap = new midcom_helper_nav();
?>
<h1>Running notification checks</h1>

<pre>
<?php
$notifier->check_topic_articles($nap->get_root_node());
?>
</pre>