<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$label = $data['label_property'];
echo "<h2>";
echo sprintf($_MIDCOM->i18n->get_string('%s trash', 'midgard.admin.asgard'), midgard_admin_asgard_plugin::get_type_label($data['type']));
echo "</h2>";

echo "<form method=\"post\">\n";
echo "<table>\n";
echo "    <thead>\n";
echo "        <tr>\n";
echo "            <th>&nbsp;</th>\n";
echo "            <th>" . $_MIDCOM->i18n->get_string('title', 'midcom') . "</th>\n";
echo "            <th>" . $_MIDCOM->i18n->get_string('deleted on', 'midgard.admin.asgard') . "</th>\n";
echo "            <th>" . $_MIDCOM->i18n->get_string('deleted by', 'midgard.admin.asgard') . "</th>\n";
echo "            <th>" . $_MIDCOM->i18n->get_string('size', 'midgard.admin.asgard') . "</th>\n";
echo "        </tr>\n";
echo "    </thead>\n";
echo "    <tbody>\n";

foreach ($data['trash'] as $object)
{
    $revisor = $_MIDCOM->auth->get_user($object->metadata->revisor);
    
    echo "        <tr>\n";
    echo "            <td><input type=\"checkbox\" name=\"undelete[]\" value=\"{$object->guid}\" /></td>\n";
    echo "            <td>{$object->$label}</td>\n";
    echo "            <td>" . strftime('%x %X', strtotime($object->metadata->revised)) . "</td>\n";
    
    if ($revisor->guid)
    {
        echo "            <td><a href=\"{$prefix}__mfa/asgard/object/view/{$revisor->guid}/\">{$revisor->name}</a></td>\n";
    }
    else
    {
        echo "            <td>&nbsp;</td>\n";
    }
    echo "            <td>" . midcom_helper_filesize_to_string($object->metadata->size) . "</td>\n";
    echo "        </tr>\n";
}

echo "    </tbody>\n";
echo "    <tfoot>\n";
echo "        <tr>\n";
echo "            <td colspan=\"5\">\n";
echo "                <input type=\"submit\" value=\"" . $_MIDCOM->i18n->get_string('undelete', 'midgard.admin.asgard') . "\" />\n";
echo "            </td>\n";
echo "        </tr>\n";
echo "    </tfoot>\n";
echo "</table>\n";
echo "</form>\n";
echo $data['qb']->show_pages();
?>