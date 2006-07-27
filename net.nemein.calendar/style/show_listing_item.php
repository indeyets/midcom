<?php
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $request_data['datamanager']->get_content_raw();
?>
<li class="vevent" id="<?php echo $request_data['event']->guid; ?>">
  <abbr class="dtstart" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $request_data['event']->start); ?>"><?php echo midcom_helper_generate_daylabel('start', $request_data['event']->start, $request_data['event']->end); ?></abbr> -
  <abbr class="dtend" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $request_data['event']->end); ?>"><?php echo midcom_helper_generate_daylabel('end', $request_data['event']->start, $request_data['event']->end); ?></abbr>
  <a class="url" href="&(request_data['event_url']);"><span class="summary">&(view['title']:h);</span></a>

  <?php 
  if (array_key_exists('location', $view)) 
  { 
    ?>
    <span class="location">&(view['location']:h);</span>
    <?php 
  } ?>
  <abbr class="dtstamp" style="display: none;" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $request_data['event']->created); ?>"><?php echo gmdate('Y-m-d\TH:i:s\Z', $request_data['event']->created); ?></abbr>
  <span class="uid" style="display: none;"><?php echo $request_data['event']->guid(); ?></span>  
</li>