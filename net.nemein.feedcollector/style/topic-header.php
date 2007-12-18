<?php
/*
*  Available request keys:
*  $data['topic']; (the topic from where the content is fetched)
*  $data['feedtopic']; (the feedcollector topic object)
*  $data['counters']; (array of counters to help you order your results)
*    topic        - count the topic we are on
*    topics       - overall count of topics to show
*    items        - overall count of items
*    topic_items  - count of items to show in this topic
* 
*/  
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$topic = $data['feedtopic'];
$url = $data['permalinks']->create_permalink($data['topic']->guid);
// counter for topic
$topic_counter = $data['counters']['topic'];
?>
<div class="net_nemein_feedcollector_topic topic_counter_&(topic_counter);">
<h1><a href="&(url);"><?php echo $data['feedtopic']['object']->title; ?></a></h1>
