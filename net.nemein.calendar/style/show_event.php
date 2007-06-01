<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_event'];
?>
<div class="vevent" id="<?php echo $data['event']->guid; ?>">
  <h1 class="summary">&(view['title']:h);</h1>

  <div class="time">
    <abbr class="dtstart" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->start); ?>"><?php echo midcom_helper_generate_daylabel('start', $data['event']->start, $data['event']->end); ?></abbr> -
    <abbr class="dtend" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->end); ?>"><?php echo midcom_helper_generate_daylabel('end', $data['event']->start, $data['event']->end); ?></abbr>
  </div>

  <?php if (array_key_exists('location', $view)) { ?>
    <div class="location">
      <?php echo $data['l10n']->get("location"); ?>: &(view['location']:h);
    </div>
  <?php } ?>

  <div class="description">
    &(view['description']:h);
  </div>
  <abbr class="dtstamp" style="display: none;" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->created); ?>"><?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->created); ?></abbr>
  <span class="uid" style="display: none;"><?php echo $data['event']->guid; ?></span>    
<?php
if ($data['archive_mode']) 
{
    $url = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'archive.html';
?>
  <p><a href="&(url);"><?php $data['l10n']->show('back to archive.'); ?></a></p>
<?php } ?>
</div>
