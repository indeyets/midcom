<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view =& $data['view'];

?>
<h1><?php echo sprintf($_MIDCOM->i18n->get_string('edit folder %s', 'midcom.admin.folder'), $data['folder']->extra); ?></h1>
<?php
if ($_MIDCOM->auth->admin)
{
    if (array_key_exists( $data['topic']->component, $_MIDCOM->componentloader->manifests ))
    {
        $path = $data['topic']->component;
    }
    else
    {
        $path = 'midcom.core.nullcomponent';
    }
    
    $component = $_MIDCOM->componentloader->manifests[$path];
    $component_name = $component->get_name_translated();

?>
    <div class="form_description">
        <?php echo $_MIDCOM->i18n->get_string('component', 'midcom'); ?>
    </div>
    <div class="form_field">
        &(component_name:h);
    </div>
<?php
}
?>

<form method="post" action="" enctype="multipart/form-data" class="midcom_admin_folder edit_folder">
    <div class="form_description">
        <label for="url_name">
            <?php echo $_MIDCOM->i18n->get_string('url name', 'midcom'); ?>
        </label>
    </div>
    <div class="form_field">
        <input class="shorttext" id="url_name" name="f_name" type="text" size="50" maxlength="255" value="<?php echo htmlspecialchars($view->name);?>" />
    </div>
    <div class="form_description">
        <label for="title">
            <?php echo $_MIDCOM->i18n->get_string('title', 'midcom'); ?>
        </label>
    </div>
    <div class="form_field">
        <input class="shorttext" id="title" name="f_title" type="text" size="50" maxlength="255" value="<?php echo htmlspecialchars($view->extra);?>" />
    </div>
    <div class="form_description">
        <label for="style">
            <?php echo $_MIDCOM->i18n->get_string('style', 'midcom.admin.folder'); ?>
        </style>
    </div>
    <div class="form_field">
        <select id="style" class="dropdown" name="f_style">
            <?php
            foreach ($data['styles'] as $style_identifier => $style)
            {
                $selected = '';
                if ($data['style'] == $style_identifier)
                {
                    $selected = ' selected="selected"';
                }
                echo "<option{$selected} value=\"{$style_identifier}\">{$style}</option>\n";
            }
            ?>
        </select>
    </div>    
    <div class="form_description">
        <label for="style_inherit">
            <?php echo $_MIDCOM->i18n->get_string('inherit style', 'midcom.admin.folder'); ?>
        </label>
    </div>
<?php
if ($data['style_inherit'])
{
    $checked = ' checked="checked"';
}
else
{
    $checked = '';
}
?>
    <div class="form_field">
        <input class="checkbox" id="style_inherit" name="f_style_inherit" type="checkbox" size="50" maxlength="5"&(checked:h); />
    </div>
    
    <!--
    <div class="form_description">
        <?php echo $_MIDCOM->i18n->get_string('score', 'midcom.admin.folder'); ?>
    </div>
    <div class="form_field">
        <input class="shorttext" name="f_score" type="text" size="50" maxlength="5" value="<?php echo $view->score;?>" />
    </div>
    -->
    
    <div class="form_description">
        <label for="nav_order">
            <?php echo $_MIDCOM->i18n->get_string('nav order', 'midcom.admin.folder'); ?>
        </label>
    </div>
    <div class="form_field">
        <select id="nav_order" class="dropdown" name="f_navorder">
<?php
foreach ($data['navorder_list'] as $value => $caption)
{
    if ($data['navorder'] == $value)
    {
        $selected = ' selected="selected"';
    }
    else
    {
        $selected = '';
    }
?>
            <option value="&(value);"&(selected:h);><?php echo htmlspecialchars($caption); ?></option>
<?php
}
?>
        </select>
    </div>
    <div class="form_description" >
        <p>
            <?php echo $_MIDCOM->i18n->get_string( 'change component', 'midcom.admin.folder' ); ?>
        </p>
        <span style="color: red;"><?php echo $_MIDCOM->i18n->get_string('warning, might result in data loss', 'midcom.admin.folder'); ?></span>
    </div>
<?php
midcom_show_style('midcom-admin-show-component-list');
?>
    <div class="form_toolbar">
        <input class="save" type="submit" accesskey="s" name="f_submit" value="<?php echo $_MIDCOM->i18n->get_string('save', 'midcom'); ?>" />
        <input class="cancel" type="submit" accesskey="c" name="f_cancel" value="<?php echo $_MIDCOM->i18n->get_string('cancel', 'midcom'); ?>" />
    </div>
</form>
