<?php
/**
 * Available keys
 * 
 * $data['column'] // Column count from left to right
 * $data['row']    // Row count of the current group
 * 
 * Note that the class names are according to vCard standard: http://microformats.org/wiki/hcard
 */
// $data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();
?>
<div class="vcard">
    <h3>
        <a class="url" href="<?php echo $data['view_url']; ?>">
        <span class="n">
            <span class="given-name">&(view['firstname']:h);</span>
            <span class="family-name">&(view['lastname']:h);</span>
        </span>
        </a>
    </h3>
    <?php
    if (isset($view['image']))
    {
        echo $view['image'];
    }
    ?>
    <span class="org" style="display: none;"><span class="organization-unit"><?php echo $data['group']->official; ?></span></span>
    <p class="title">&(view['title']:h);</p>
    <p class="tel">
        <?php $data['l10n']->get('workphone'); ?>
        <span class="type" style="display: none;">work</span>
        <span class="value">&(view['workphone']:h);</span>
    </p>
<?php
if ($view['email'])
{
?>
    <p>
        <?php echo $data['l10n']->get('email'); ?>
        <a class="email" href="mailto:&(view['email']:h);">&(view['email']:h);</a>
    </p>
<?php
}
?>
</div>
