<h1><?php echo $data['title']; ?></h1>
<?php
if (@$data['error_message'])
{
    echo "<p style=\"color: red;\">{$data['error_message']}</p>\n";
}
?>
<form method="post" action="" enctype="multipart/form-data" class="midcom_admin_folder create_folder">
    <div class="form_description">
        <label for="url_name">
            <?php echo $_MIDCOM->i18n->get_string('url name', 'midcom'); ?>
        </label>
    </div>
    <div class="form_field">
        <input id="url_name" class="shorttext" name="f_name" type="text" size="50" maxlength="255" value="&(data['folder_name']:h);" />
    </div>
    <div class="form_description">
        <label for="title">
            <?php echo $_MIDCOM->i18n->get_string('title', 'midcom'); ?>
        </label>
    </div>
    <div class="form_field">
        <input class="shorttext" id="title" name="f_title" type="text" size="50" maxlength="255" value="&(data['folder_title']:h);" />
    </div>
    <div class="form_description">
        <?php echo $_MIDCOM->i18n->get_string('folder type', 'midcom.admin.folder'); ?>
    </div>
<?php
midcom_show_style('midcom-admin-show-component-list');
?>
<?php
/*
?>
    <div class="form_description">
        <label for="style">
            <?php echo $_MIDCOM->i18n->get_string('style', 'midcom.admin.folder'); ?>
        </label>
    </div>
    <div class="form_field">
        <select id="style" class="dropdown" name="f_style">
            <?php
            foreach ($data['styles'] as $style_identifier => $style)
            {
                echo "<option value=\"{$style_identifier}\">{$style}</option>\n";
            }
            ?>
        </select>
    </div>    
<?php
/* */
?>
    <div class="form_description">
        <label for="navorder">
            <?php echo $_MIDCOM->i18n->get_string('nav order', 'midcom.admin.folder'); ?>
        </label>
    </div>
    <div class="form_field">
        <select id="navorder" class="dropdown" name="f_navorder">
            <?php
            foreach ($data['navorder_list'] as $value => $caption)
            {
                if ($data['parent_navorder'] == $value)
                {
                    $selected = ' selected="selected"';
                }
                else
                {
                    $selected = '';
                }
                ?>
                <option value="<?php echo htmlspecialchars($value); ?>"&(selected:h);>&(caption);</option>
                <?php
            }
            ?>
        </select>
    </div>
    
    <div class="form_toolbar">
        <input class="save" type="submit" accesskey="s" name="f_submit" value="<?php echo $_MIDCOM->i18n->get_string('save', 'midcom'); ?>" />
        <input class="cancel" type="submit" accesskey="c" name="f_cancel" value="<?php echo $_MIDCOM->i18n->get_string('cancel', 'midcom'); ?>" />
    </div>
</form>
