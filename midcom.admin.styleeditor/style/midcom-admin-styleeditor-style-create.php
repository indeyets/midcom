<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');

$name = '';
$value = '';

if (array_key_exists('midcom_admin_styleeditor_style_name',$_POST))
{
	$name = $_POST['midcom_admin_styleeditor_style_name'];
	$value = $_POST['midcom_admin_styleeditor_style_edit'];
}
?>
<h1><?php echo $_MIDCOM->i18n->get_string('create a new element', 'midcom.admin.styleeditor'); ?></h1>
<div id="midcom_admin_styleeditor_style">
	<div class="message">&(data['message']);</div>
    <form class="midcom_admin_styleeditor_styleeditor" method="post" action="." enctype="multipart/form-data">
        <fieldset>
        	<legend><?php echo $_MIDCOM->i18n->get_string('element name', 'midcom.admin.styleeditor'); ?></legend>
        	<input type="text" name="midcom_admin_styleeditor_style_name" id="midcom_admin_styleeditor_style_name" class="name" value="&(name);" />
        </fieldset>
        <fieldset>                                                                                                                            
            <legend><?php echo $_MIDCOM->i18n->get_string('element from file', 'midcom.admin.styleeditor'); ?></legend>                            
            <input type="file" name="midcom_admin_styleeditor_style_file" id="midcom_admin_styleeditor_style_file" class="file" />            
        </fieldset> 
        <fieldset> 
            <legend><?php echo $_MIDCOM->i18n->get_string('local element', 'midcom.admin.styleeditor'); ?></legend>
            <textarea cols="60" rows="20" id="midcom_admin_styleeditor_style_edit" name="midcom_admin_styleeditor_style_edit" class="element">&(value);</textarea>
            <div class="form_toolbar">
                <input type="submit" class="save" accesskey="s" value="<?php echo $_MIDCOM->i18n->get_string('save', 'midcom'); ?>" />
            </div>
        </fieldset>
    </form>
</div>