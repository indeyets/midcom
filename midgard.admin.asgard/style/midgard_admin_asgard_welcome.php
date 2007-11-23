<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$revised_after_choices = array();
// 1 day
$date = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
$revised_after_choices[$date] = $_MIDCOM->i18n->get_string('1 day', 'midgard.admin.asgard');
// 1 week
$date = mktime(0, 0, 0, date('m'), date('d') - 6, date('Y'));
$revised_after_choices[$date] = $_MIDCOM->i18n->get_string('1 week', 'midgard.admin.asgard');
// 1 month
$date = mktime(0, 0, 0, date('m') - 1, date('d'), date('Y'));
$revised_after_choices[$date] = $_MIDCOM->i18n->get_string('1 month', 'midgard.admin.asgard');

?>

<div id="latest_objects">
    
    <div class="filter">
        <form name="latest_objects_filter" method="GET">
            <div class="revised_after">
                <label for="revised_after"><?php echo $_MIDCOM->i18n->get_string('objects revised within', 'midgard.admin.asgard'); ?></label>
                <select name="revised_after" id="revised_after">
                    <?php
                    foreach ($revised_after_choices as $value => $label)
                    {
                        $selected = '';
                        if (   isset($data['revised_after'])
                            && $data['revised_after'] == date('Y-m-d H:i:s\Z', $value))
                        {
                            $selected = ' selected="selected"';
                        }
                        echo "<option value=\"{$value}\"{$selected}>{$label}</option>\n";
                    }
                    ?>
                </select>
            </div>
            <input type="submit" name="filter" value="<?php echo $_MIDCOM->i18n->get_string('filter', 'midgard.admin.asgard'); ?>" />
        </form>
    </div>
    
    <form name="latest_objects_mass_action" method="POST">
<?php
if (count($data['revised']) > 0)
{
    $revisors = array();
    echo "<table class=\"results\">\n";
    echo "    <thead>\n";
    echo "        <tr>\n";
    echo "            <th class=\"selection\">&nbsp;</th>\n";
    echo "            <th class=\"icon\">&nbsp;</th>\n";
    echo "            <th class=\"title\">" . $_MIDCOM->i18n->get_string('title', 'midcom') . "</th>\n";
    echo "            <th class=\"revised\">" . $_MIDCOM->i18n->get_string('revised', 'midcom.admin.folder') . "</th>\n";
    echo "            <th class=\"revisor\">" . $_MIDCOM->i18n->get_string('revisor', 'midcom.admin.folder') . "</th>\n";
    echo "            <th class=\"approved\">" . $_MIDCOM->i18n->get_string('approved', 'midcom.admin.folder') . "</th>\n";
    echo "            <th class=\"revision\">" . $_MIDCOM->i18n->get_string('revision', 'midcom.admin.folder') . "</th>\n";
    echo "        </tr>\n";
    echo "    </thead>\n";
    echo "    <tbody>\n";
    
    foreach ($data['revised'] as $object)
    {
        $class = get_class($object);
        $approved = $object->metadata->approved;
        $approved_str = strftime('%x %X', $approved);
        if ($approved == 0)
        {
            $approved_str = $_MIDCOM->i18n->get_string('not approved', 'midgard.admin.asgard');
        }
        $title = substr($data['reflectors'][$class]->get_object_label(&$object), 0, 60);
        if (empty($title))
        {
            $title = '[' . $_MIDCOM->i18n->get_string('no title', 'midgard.admin.asgard') . ']';
        }
        
        if (!isset($revisors[$object->metadata->revisor]))
        {
            $revisors[$object->metadata->revisor] = $_MIDCOM->auth->get_user($object->metadata->revisor);
        }
        
        echo "        <tr>\n";
        echo "            <td class=\"selection\"><input type=\"checkbox\" name=\"selections[]\" value=\"{$object->guid}\" /></td>\n";
        //{$object->metadata->revised}_{$object->guid}_{$object->metadata->revision}
        echo "            <td class=\"icon\">" . $data['reflectors'][$class]->get_object_icon(&$object) . "</td>\n";
        echo "            <td class=\"title\"><a href=\"{$prefix}__mfa/asgard/object/view/{$object->guid}/\" title=\"{$class}\">" . $title . "</a></td>\n";
        echo "            <td class=\"revised\">" . strftime('%x %X', $object->metadata->revised) . "</td>\n";
        echo "            <td class=\"revisor\">{$revisors[$object->metadata->revisor]->name}</td>\n";
        echo "            <td class=\"approved\">{$approved_str}</td>\n";
        echo "            <td class=\"revision\">{$object->metadata->revision}</td>\n";
        echo "        </tr>\n";
    }
    
    echo "    </tbody>\n";
    echo "</table>\n";
?>
        <div class="actions">
            <div class="action">
                <select name="mass_action" id="mass_action">
                    <option value=""><?php echo $_MIDCOM->i18n->get_string('choose action', 'midgard.admin.asgard'); ?></option>
                    <option value="delete"><?php echo $_MIDCOM->i18n->get_string('delete', 'midcom'); ?></option>
                    <option value="approve"><?php echo $_MIDCOM->i18n->get_string('approve', 'midcom'); ?></option>
                </select>
            </div>
            <input type="submit" name="execute_mass_action" value="<?php echo $_MIDCOM->i18n->get_string('apply to selected', 'midgard.admin.asgard'); ?>" />    
        </div>
    </form>
    
<?php
}
?>

</div>