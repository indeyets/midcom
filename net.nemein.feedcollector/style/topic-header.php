<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$topic = new midcom_db_topic($data['topic']['object']->feedtopic);
$url = $_MIDCOM->permalinks->create_permalink($topic->guid);
// counter for topic
$topic_counter = $data['counters']['topic'];
?>
<div class="net_nemein_feedcollector_topic topic_counter_&(topic_counter);">
<h1><a href="&(url);"><?php echo $data['topic']['object']->title; ?></a></h1>
