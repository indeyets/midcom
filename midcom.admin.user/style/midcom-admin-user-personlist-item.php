<tr<?php if ($data['even']) { echo ' class="even"'; } ?>>
    <?php
    $checked = '';
    if (isset($_POST['midcom_admin_user'])
        && is_array($_POST['midcom_admin_user'])
        && in_array($data['person']->id, $_POST['midcom_admin_user']))
    {
        $checked = ' checked="checked"';
    }
    
    if (!$data['person']->can_do('midgard:update'))
    {
        $checked .= ' disabled="disabled"';
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
    
    $qb = midcom_db_member::new_query_builder();
    $qb->add_constraint('uid', '=', $data['person']->id);
    $memberships = $qb->execute();
    $groups = array();
    foreach ($memberships as $member)
    {
        if (!is_object($data['groups'][$member->gid]))
        {
            $groups[] = $data['groups'][$member->gid];
        }
        else
        {
            $groups[] = "<a href=\"{$prefix}__mfa/asgard_midcom.admin.user/group/edit/{$data['groups'][$member->gid]->guid}/\">{$data['groups'][$member->gid]->official}</a>";
        }
    }
    echo "<td>" . implode(', ', $groups) . "</td>\n";
    ?>
</tr>