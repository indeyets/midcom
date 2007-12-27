<?php
$_MIDCOM->auth->require_valid_user();

$mc = new midgard_collector('net_nemein_attention_click', 'person', $_MIDGARD['user']);
$mc->set_key_property('guid');
$mc->add_value_property('hostname');
$mc->execute();
$clicks = $mc->list_keys();
foreach ($clicks as $click_guid => $value)
{
    $site_identifier = $mc->get_subkey($click_guid, 'hostname');
    if (!isset($sites[$site_identifier]))
    {
        $sites[$site_identifier] = 0;
    }
    $sites[$site_identifier]++;

}

arsort($sites);

echo "<table>\n";
echo "    <thead>\n";
echo "        <tr>\n";
echo "            <th>" . $_MIDCOM->i18n->get_string('website', 'net.nemein.attention') . "</th>\n";
echo "            <th>" . $_MIDCOM->i18n->get_string('visits', 'net.nemein.attention') . "</th>\n";
echo "        </tr>\n";
echo "    </thead>\n";
echo "    <tbody>\n";
foreach ($sites as $id => $count)
{
    echo "        <tr>\n";
    echo "            <td>{$id}</td>\n";
    echo "            <td>{$count}</td>\n";
    echo "        </tr>\n";
}
echo "    </tbody>\n";
echo "</table>\n";
?>