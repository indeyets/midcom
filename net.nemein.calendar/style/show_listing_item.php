<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_raw();
?>
<li class="vevent" id="<?php echo $data['event']->guid; ?>">
  <abbr class="dtstart" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->start); ?>"><?php echo midcom_helper_generate_daylabel('start', $data['event']->start, $data['event']->end); ?></abbr> -
  <abbr class="dtend" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->end); ?>"><?php echo midcom_helper_generate_daylabel('end', $data['event']->start, $data['event']->end); ?></abbr>
  <a class="url" href="&(data['event_url']);"><span class="summary">&(view['title']:h);</span></a>

  <?php 
  if (array_key_exists('location', $view)) 
  { 
    ?>
    <span class="location">&(view['location']:h);</span>
    <?php 
  } ?>
  <abbr class="dtstamp" style="display: none;" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->created); ?>"><?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->created); ?></abbr>
  <span class="uid" style="display: none;"><?php echo $data['event']->guid(); ?></span>  
</li>