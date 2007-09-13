<?php
$view = $data['datamanager']->get_content_raw();
?>
<li class="vevent" id="<?php echo $data['event']->guid; ?>">
    <div class="dates">
        <abbr class="dtstart" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->start); ?>"><?php echo midcom_helper_generate_daylabel('start', $data['event']->start, $data['event']->end); ?></abbr> -
        <abbr class="dtend" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->end); ?>"><?php echo midcom_helper_generate_daylabel('end', $data['event']->start, $data['event']->end); ?></abbr>
    </div>
    <div class="summary"><a class="url" href="&(data['event_url']);">&(view['title']:h);</a></div>
    <?php 
    if (array_key_exists('location', $view)) 
    { 
        ?>
        <div class="location">&(view['location']:h);</div>
        <?php 
    } 
    ?>
    <abbr class="dtstamp" style="display: none;" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->metadata->published); ?>"><?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->metadata->published); ?></abbr>
    <span class="uid" style="display: none;"><?php echo $data['event']->guid; ?></span>  
</li>