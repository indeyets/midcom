<?php
$view = $data['salesproject_dm'];
?>
<div class="main">
    <h1><?php echo $data['l10n']->get('create salesproject'); ?></h1>
    <?php $view->display_form(); ?>
</div>