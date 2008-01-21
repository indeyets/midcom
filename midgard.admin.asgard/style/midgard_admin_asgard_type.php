<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
if ($data['component'] == 'midgard')
{
    $component_label = 'Midgard CMS';
}
else
{
    $component_label = $_MIDCOM->i18n->get_string($data['component'], $data['component']);
}
echo "<h2>";
echo sprintf($_MIDCOM->i18n->get_string('%s in %s', 'midcom'),
        midgard_admin_asgard_plugin::get_type_label($data['type']),
        $component_label);
echo "</h2>";

if ($data['component'] == 'midgard')
{
    echo "<p>" . $_MIDCOM->i18n->get_string('this is a midgard core type', 'midgard.admin.asgard') . "</p>\n";
}
else
{
    echo "<p>" . sprintf($_MIDCOM->i18n->get_string('this type belongs to %s component', 'midgard.admin.asgard'), $data['component']) . "</p>\n";
}
?>

&(data['help']:h);

<form method="get">
    <label>
        <?php echo $_MIDCOM->i18n->get_string('search', 'midgard.admin.asgard'); ?>
        <input type="text" name="search" class="search"<?php if (isset($_GET['search'])) { echo " value=\"{$_GET['search']}\""; } ?> />
    </label>
    <input class="search" type="submit" value="<?php echo $_MIDCOM->i18n->get_string('go', 'midgard.admin.asgard'); ?>" />
</form>
<?php
if (isset($data['search_results']))
{
    if (!$data['search_results'])
    {
        echo "<p>" . $_MIDCOM->i18n->get_string('no results', 'midgard.admin.asgard') . "</p>\n";
    }

    else
    {
        echo "<table id=\"search_results\">\n";
        echo "    <thead>\n";
        echo "        <tr>\n";
        echo "            <th>" . $_MIDCOM->i18n->get_string('title', 'midcom') . "</th>\n";
        echo "            <th>" . $_MIDCOM->i18n->get_string('created on', 'midgard.admin.asgard') . "</th>\n";
        echo "            <th>" . $_MIDCOM->i18n->get_string('created by', 'midgard.admin.asgard') . "</th>\n";
        echo "        </tr>\n";
        echo "    </thead>\n";
        echo "    <tbody>\n";
        $persons = array();
        foreach ($data['search_results'] as $result)
        {
            $reflector = midcom_helper_reflector_tree::get($result);
            $icon = $reflector->get_object_icon($result);
            $label = $reflector->get_label_property();
            if (!isset($persons[$result->metadata->creator]))
            {
                $persons[$result->metadata->creator] = $_MIDCOM->auth->get_user($result->metadata->creator);
            }

            echo "        <tr>\n";
            echo "            <td><a href=\"{$prefix}__mfa/asgard/object/view/{$result->guid}/\">{$icon} {$result->$label}</a></td>\n";
            echo "            <td>" . strftime('%x %X', $result->metadata->created) . "</td>\n";

            if ($persons[$result->metadata->creator]->guid)
            {
                echo "            <td><a href=\"{$prefix}__mfa/asgard/object/view/{$persons[$result->metadata->creator]->guid}/\">{$persons[$result->metadata->creator]->name}</a></td>\n";
            }
            else
            {
                echo "            <td>&nbsp;</td>\n";
            }

            echo "        </tr>\n";
        }
        echo "    </tbody>\n";
        echo "</table>\n";
        echo "<script type=\"text/javascript\">\n";
        echo "        // <![CDATA[\n";
        echo "            \$j('#search_results').tablesorter(\n";
        echo "            {\n ";
        echo "                widgets: ['zebra'],";
        echo "                sortList: [[0,0]],\n";
        echo "            });\n";
        echo "        // ]]>\n";
        echo "    </script>\n";
    }
}
?>