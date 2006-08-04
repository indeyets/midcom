<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');

function array2code($arr, $level=0, $code = '')
{
    $pad1 = '';
    $d = $level * 4;
    while ($d--)
    {
        $pad1 .= ' ';
    }
    $pad2 = '';
    $d = ($level+1) * 4;
    while ($d--)
    {
        $pad2 .= ' ';
    }
    $code .= "Array\n{$pad1}(\n";
    foreach ($arr as $k => $v)
    {
        $code .= $pad2;
        switch (true)
        {
            case is_numeric($k):
                $code .= "{$k} => ";
                break;
            default:
                $code .= "'{$k}' => ";
                break;
        }
        switch (true)
        {
            case is_array($v):
                $code = array2code($v, $level+2, $code);
                break;
            default:
                $code .= "'{$v}',\n";
                break;
        }
    }
    $code .= "{$pad1})";
    if ($level > 0)
    {
        $code .= ",\n";
    }
    return $code;
}

if (isset($_POST['midcom_helper_datamanager_dummy_field_rules']))
{
    $editor_content = $_POST['midcom_helper_datamanager_dummy_field_rules'];
}
else
{
    $editor_content = array2code($view_data['campaign']->rules);
}

?>
<div class="main">
    <form name="midcom_helper_datamanager__form" enctype="multipart/form-data" method="post" class="datamanager">
        <fieldset class="area">
            <legend>rules</legend>
            <label for="midcom_helper_datamanager_dummy_field_rules" id="midcom_helper_datamanager_dummy_field_rules_label">                <span class="field_text">rules</span>
                <textarea id="midcom_helper_datamanager_dummy_field_rules" name="midcom_helper_datamanager_dummy_field_rules" class="longtext">&(editor_content:s);</textarea>
            </label>
        </fieldset>
        <div class="form_toolbar">            <input name="midcom_helper_datamanager_submit" accesskey="s" class="save" value="save" type="submit">            <input name="midcom_helper_datamanager_cancel" class="cancel" value="cancel" type="submit">        </div>
    </form>
</div>
<div class="sidebar">
    <div class="area">
    </div>
</div>
