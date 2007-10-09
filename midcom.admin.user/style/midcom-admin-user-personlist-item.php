<tr>
    <?php
    $checked = '';
    if (isset($_POST['midcom_admin_user'])
        && is_array($_POST['midcom_admin_user'])
        && in_array($data['person']->id, $_POST['midcom_admin_user']))
    {
        $checked = ' checked="checked"';
    }
    ?>
    <td><input type="checkbox" name="midcom_admin_user[]" value="<?php echo $data['person']->id; ?>" <?php echo $checked; ?>/></td>
    <?php
    $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
    $linked = 0;
    foreach ($data['list_fields'] as $field)
    {
        $value = $data['person']->$field;
        if (   $linked < 2
            && $data['person']->can_do('midgard:update'))
        {
            if (!$value)
            {
                $value = "&lt;{$field}&gt;";
            }
            $value = "<a href=\"{$prefix}__mfa/asgard_midcom.admin.user/edit/{$data['person']->guid}/\">{$value}</a>";
            $linked++;
        }
        echo "<td>{$value}</td>\n";
    }
    ?>
</tr>