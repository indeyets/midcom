<a name="all"></a>
<fieldset id="all">
        <legend onclick="javascript:toggle_twisty('all_contents');">
                <?php echo $_MIDCOM->i18n->get_string('all style elements', 'midcom.admin.styleeditor'); ?>
            <img class="twisty" src="<?php echo MIDCOM_STATIC_URL; ?>/midcom.admin.styleeditor/twisty-<?php echo ($data['display'] === 'none') ? 'hidden' : 'down'; ?>.gif" alt="-" />
        </legend>
    <div id="all_contents" style="display: &(data['display']:h);" class="description all-components">
        <div class="wrapper">
