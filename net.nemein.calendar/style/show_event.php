<?php
// Bind the view data, remember the reference assignment:
$view = $data['view_event'];
$data['event']->start = strtotime($data['event']->start);
$data['event']->end = strtotime($data['event']->end);
?>
<div class="vevent" id="<?php echo $data['event']->guid; ?>">
    <h1 class="summary">&(view['title']:h);</h1>

    <div class="time">
        <abbr class="dtstart" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->start); ?>"><?php echo midcom_helper_generate_daylabel('start', $data['event']->start, $data['event']->end, true, true); ?></abbr> -
        <abbr class="dtend" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->end); ?>"><?php echo midcom_helper_generate_daylabel('end', $data['event']->start, $data['event']->end, true, true); ?></abbr>
    </div>

    <?php
    if (   array_key_exists('location', $view)
        && trim(strip_tags($view['location'])) != '')
    { 
        ?>
        <div class="loc">
            <?php echo $data['l10n']->get("location"); ?>: <span class="location">&(view['location']:h);</span>
        </div>
        <?php 
    } 
    ?>

    <div class="description">
        &(view['description']:h);
    </div>
    <abbr class="dtstamp" style="display: none;" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->metadata->published); ?>"><?php echo gmdate('Y-m-d\TH:i:s\Z', $data['event']->metadata->published); ?></abbr>
    <span class="uid" style="display: none;"><?php echo $data['event']->guid; ?></span>    
</div>
<?php
if ($data['archive_mode']) 
{
    $url = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'archive/';
    ?>
    <p><a href="&(url);"><?php $data['l10n']->show('back to archive.'); ?></a></p>
    <?php 
} 
?>