<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
if (count($data['revised']) > 0)
{
    $revisors = array();
    echo "<table>\n";
    echo "    <thead>\n";
    echo "        <tr>\n";
    echo "            <th>&nbsp;</th>\n";
    echo "            <th>" . $_MIDCOM->i18n->get_string('title', 'midcom') . "</th>\n";
    echo "            <th>" . $_MIDCOM->i18n->get_string('revised', 'midcom.admin.folder') . "</th>\n";
    echo "            <th>" . $_MIDCOM->i18n->get_string('revisor', 'midcom.admin.folder') . "</th>\n";
    echo "            <th>" . $_MIDCOM->i18n->get_string('revision', 'midcom.admin.folder') . "</th>\n";
    echo "        </tr>\n";
    echo "    </thead>\n";
    echo "    <tbody>\n";
    foreach ($data['revised'] as $object)
    {
        $class = get_class($object);
        
        if (!isset($revisors[$object->metadata->revisor]))
        {
            $revisors[$object->metadata->revisor] = $_MIDCOM->auth->get_user($object->metadata->revisor);
        }
        
        echo "        <tr>\n";
        echo "            <td>" . $data['reflectors'][$class]->get_object_icon(&$object) . "</td>\n";
        echo "            <td><a href=\"{$prefix}__mfa/asgard/object/view/{$object->guid}/\" title=\"{$class}\">" . substr($data['reflectors'][$class]->get_object_label(&$object), 0, 60) . "</a></td>\n";
        echo "            <td>" . strftime('%x %X', $object->metadata->revised) . "</td>\n";
        echo "            <td>{$revisors[$object->metadata->revisor]->name}</td>\n";
        echo "            <td>{$object->metadata->revision}</td>\n";
        echo "        </tr>\n";
    }
    echo "    </tbody>\n";
    // TODO: Actions here
    echo "</table>\n";
}
?>