<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>q'n'd index</h1>
<h2>hostgroups</h2>
<ul>
<?php
$qb = net_nemein_netmon_hostgroup_dba::new_query_builder();
$qb->add_order('title');
$qb->add_order('name');
$hostgroups = $qb->execute();
unset($qb);
foreach ($hostgroups as $hostgroup)
{
    echo "    <li><a href='{$prefix}hostgroup/{$hostgroup->guid}.html'>{$hostgroup->title}</a></li>\n";
}
?>
</ul>
<h2>hosts</h2>
<ul>
<?php
$qb = net_nemein_netmon_host_dba::new_query_builder();
$qb->add_order('title');
$qb->add_order('name');
$hosts = $qb->execute();
unset($qb);
foreach ($hosts as $host)
{
    echo "    <li><a href='{$prefix}host/{$host->guid}.html'>{$host->title}</a></li>\n";
}
?>
</ul>
