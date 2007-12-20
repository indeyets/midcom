<?php
$component =& $data['component_data'];
?>
<li><h3><img src="<?php echo MIDCOM_STATIC_URL; ?>/&(component['icon']);" alt="" /> &(component['name']);</h3>
    <span class="version">&(component['version']);</span>
    <span class="description">&(component['title']);</span>
    <?php echo $component['toolbar']->render(); ?>
</li>
