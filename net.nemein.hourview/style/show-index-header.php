<?php
global $view_topic, $view_messages;
?>
<h1>&(view_topic.extra);</h1>

<form method="post" class="datamanager net_nemein_hourview">

<?php
if (count($view_messages) > 0)
{
    echo "<div class=\"processing_message\">\n";
    foreach ($view_messages as $message)
    {
        echo $message."<br />\n";
    }
    echo "</div>\n";
}
?>