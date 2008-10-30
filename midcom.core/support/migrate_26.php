<?php
/**
 * @author Eero af Heurlin
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
require_once('Console/Getargs.php');
error_reporting(E_ALL);

$component_map = array
(
    'de.linkm.taviewer' => 'net.nehmer.static',
    'de.linkm.newsticker' => 'net.nehmer.blog',
    'net.siriux.photos' => 'net.siriux.photos-convertme-org.routamc.gallery',
);


$opts_config =array(); 
$opts_config['configuration'] = array (
            'short' => 'c',
            'max'   => 1,
            'min'   => 0,
            'desc'  => 'Name of the midgard configuration file.',
            'default' => 'midgard.conf',
        );  
$opts_config['user'] = array (
            'short' => 'u',
            'max'   => 1,
            'min'   => 1,
            'desc'  => 'Username to log in with',
        );
$opts_config['password'] = array (
            'short' => 'p',
            'max'   => 1,
            'min'   => 1,
            'desc'  => 'password to log in with',
            
        );
$opts_config['verbose'] = array (
            'short' => 'v',
            'max'   => 1,
            'min'   => 0,
            'desc'  => 'Be verbose',
            'default' => true,
            
        );

/*  */                
$args = Console_Getargs::factory($opts_config);
if (PEAR::isError($args)) 
{
    $header = "Usage: " .basename($GLOBALS['argv'][0])." [options]\n\n" ;
    if ($args->getCode() === CONSOLE_GETARGS_ERROR_USER) 
    {
        echo Console_Getargs::getHelp($opts_config, $header , $args->getMessage())."\n";
    }
    else if ($args->getCode() === CONSOLE_GETARGS_HELP) 
    {
        
        echo Console_Getargs::getHelp($opts_config, $header)."\n";
    }

    exit;
}


if ($args->isDefined('configuration'))
{
    $configfile = $args->getValue('configuration');
}
else
{
    $configfile = 'midgard';
}
echo "Starting midgard with config file: " . $configfile. "\n";
mgd_config_init($configfile);

if (! mgd_auth_midgard($args->getValue('user'),$args->getValue('password'),false) ) 
{
    echo "Could not log in. Exiting \n";
    exit;
}

// Map components
$qb_component = new midgard_query_builder('midgard_parameter');
$qb_component->add_constraint('domain', '=', 'midcom');
$qb_component->add_constraint('name', '=', 'component');
$qb_component->add_constraint('tablename', '=', 'topic');
$component_params = $qb_component->execute();
foreach ($component_params as $param)
{
    if (   array_key_exists($param->value, $component_map)
        && !empty($component_map[$param->value]))
    {
        $newcomponent = $component_map[$param->value];
        $newparam = new midgard_parameter();
        $newparam->domain = 'midcom';
        $newparam->name = 'component';
        $newparam->value = $newcomponent;
        $newparam->tablename = 'topic';
        $newparam->oid = $param->oid;
        $newparam->parentguid = $param->parentguid;
        
        echo "Debug: Changing topic #{$param->oid} component\n";
        $param->domain = 'midcom_24';
        if (!$param->update())
        {
            echo "ERROR: Could not update old parameter #{$param->id}, errstr: " . mgd_errstr() . "\n";
            continue;
        }
        if (!$newparam->create())
        {
            echo "ERROR: Could not create new parameter for topic #{$newparam->oid} (component '{$newcomponent}'), errstr: " . mgd_errstr() . "\n";
            continue;
        }
        echo "Info: changed topic #{$param->oid} component from '{$param->value}' to '{$newparam->value}'\n";
    }
    else
    {
        echo "Warning: cannot map old component {$param->value} to new (topic #{$param->oid})\n";
    }
}

// Clear style parameters
$qb_style = new midgard_query_builder('midgard_parameter');
$qb_style->add_constraint('domain', '=', 'midcom');
$qb_style->add_constraint('tablename', '=', 'topic');
$qb_style->begin_group('OR');
    $qb_style->add_constraint('name', '=', 'style');
    $qb_style->add_constraint('name', '=', 'style_inherit');
$qb_style->end_group();
$style_params = $qb_style->execute();
foreach ($style_params as $param)
{
    echo "Debug: clearing {$param->name} from topic #{$param->oid}\n";
    $param->domain = 'midcom_24';
    if (!$param->update())
    {
        echo "ERROR: Could not update old parameter #{$param->id}, errstr: " . mgd_errstr() . "\n";
        continue;
    }
    echo "Info: cleared {$param->name} from topic #{$param->oid}\n";
}

// Convert topic owner to midcom_core_privilege_db object
$qb_topic = new midgard_query_builder('midgard_topic');
$qb_topic->add_constraint('owner', '<>', 0);
$topics = $qb_topic->execute();
$group_cache = array();
foreach ($topics as $topic)
{
    if (!isset($group_cache[$topic->owner]))
    {
        $group = new midgard_group();
        $group->get_by_id($topic->owner);
        $group_cache[$topic->owner] = $group;
    }
    $group =& $group_cache[$topic->owner];
    if (empty($group->guid))
    {
        echo "ERROR: Could not fetch topic #{$topic->id} owner (#{$topic->owner})\n";
        continue;
    }
    $acl = new midcom_core_privilege_db();
    $acl->objectguid = $topic->guid;
    $acl->name = 'midgard:owner';
    $acl->assignee = "group:{$group->guid}";
    $acl->value = 1; //MIDCOM_PRIVILEGE_ALLOW
    if (!$acl->create())
    {
        echo "ERROR: could not create '{$acl->name} / {$acl->value}' privilege for '{$acl->assignee}' (concerning object {$acl->objectguid})\n";
    }
    echo "INFO: Converted topic #{$topic->id} owner (#{$topic->id}) to ACL\n";
}

// Convert old ViewerGroups parameters to midcom_core_privilege_db objects
$qb_vg = new midgard_query_builder('midgard_parameter');
$qb_vg->add_constraint('domain', '=', 'ViewerGroups');
$qb_vg->add_constraint('tablename', '=', 'topic');
$qb_vg->add_order('oid', 'ASC');
$vg_params = $qb_vg->execute();
$vg_map = array();
foreach ($vg_params as $vg)
{
    if (!isset($vg_map[$vg->oid]))
    {
        $vg_map[$vg->oid] = array();
    }
    $vg_map[$vg->oid][$vg->name] = $vg->value;
}
$topic_cache = array();
foreach ($vg_map as $topic_id => $grp_guids)
{
    if (!isset($topic_cache[$topic_id]))
    {
        $topic = new midgard_topic();
        $topic->get_by_id($topic_id);
        $topic_cache[$topic_id] = $topic;
    }
    // Any other privileges that may be inherited stupidly and possibly override the midgard:read
    $names = array ('midgard:read', 'midcom.admin.folder:topic_management');
    $topic =& $topic_cache[$topic_id];
    foreach ($names as $name)
    {
        $acl = new midcom_core_privilege_db();
        $acl->objectguid = $topic->guid;
        $acl->name = $name;
        $acl->assignee = 'EVERYONE';
        $acl->value = 2; //MIDCOM_PRIVILEGE_DENY
        if (!$acl->create())
        {
            echo "ERROR: could not create '{$acl->name} / {$acl->value}' privilege for '{$acl->assignee}' (concerning object {$acl->objectguid})\n";
            continue;
        }
    }
    foreach ($grp_guids as $grp_guid)
    {
        $acl = new midcom_core_privilege_db();
        $acl->objectguid = $topic->guid;
        $acl->name = 'midgard:read';
        if ($grp_guid == 'all')
        {
            $acl->assignee = 'USERS';
        }
        else
        {
            $acl->assignee = "group:{$grp_guid}";
        }
        $acl->value = 1; //MIDCOM_PRIVILEGE_ALLOW
        if (!$acl->create())
        {
            echo "ERROR: could not create '{$acl->name} / {$acl->value}' privilege for '{$acl->assignee}' (concerning object {$acl->objectguid})\n";
            continue;
        }
        echo "INFO: Converted ViewerGroup {$grp_guid} to ACL (on topic #{$topic->id})\n";
    }
}

// Convert DM1 attachment types to DM2
function mgd_param_domain_replace($obj, $from, $to)
{
    $params = $obj->listparameters($from);
    while ($params->fetch())
    {
        if (   !$obj->parameter($to, $params->name, $params->value)
            || !$obj->parameter($from, $params->name, ''))
        {
            echo "ERROR: could not move parameter('{$to}', '{$params->name}', '{$params->value}'), from '{$from}' (in object {$obj->guid})\n";
            continue;
        }   
        echo "INFO: moved parameter('{$to}', '{$params->name}', '{$params->value}'), from '{$from}' (in object {$obj->guid})\n";
    }
}
function mgd_get_att_parent_guid($obj)
{
    $att = new midgard_attachment();
    $att->get_by_id($obj->id);
    return $att->parentguid;
}
$qb_dm1p = new midgard_query_builder('midgard_parameter');
$qb_dm1p->begin_group('OR');
    $qb_dm1p->add_constraint('domain', '=', 'midcom.helper.datamanager.datatype.blob');
    //$qb_dm1p->add_constraint('domain', '=', 'midcom.helper.datamanager.datatype.blob');
$qb_dm1p->end_group();
$qb_dm1p->add_constraint('name', '=', 'fieldname');
$qb_dm1p->add_constraint('tablename', '=', 'blobs');
$qb_dm1p->add_order('oid', 'ASC');
$att_params = $qb_dm1p->execute();
echo count($att_params) . "\n";
$seen_att = array();
foreach ($att_params as $param)
{
    if (isset($seen_att[$param->oid]))
    {
        continue;
    }
    /*
    $att = new midgard_attachment();
    $att->get_by_id($param->oid);
    */
    $att = mgd_get_attachment($param->oid);
    if (!$att->id)
    {
        echo "ERROR: could not fetch attachment object id #{$param->oid}\n";
        continue;
    }
    /*
    echo "DEBUG: got att\n===\n";
    print_r($att);
    echo "===\n";
    exit();
    */
    echo "INFO: processing attachment #{$att->id}\n";
    $seen_att[$att->id] = true;
    $type = 'blobs';
    $dm1_parent_guid = $att->parameter('midcom.helper.datamanager.datatype.image', 'parent_guid');
    $dm1_thumb_guid = $att->parameter('midcom.helper.datamanager.datatype.image', 'thumbguid');
    $collection_fieldname = $att->parameter('midcom.helper.datamanager.datatype.collection', 'fieldname');
    // Image type needs special treatment.
    if (   $dm1_parent_guid
        || $dm1_thumb_guid)
    {
        $type = 'image';
        // Juggle parent_att vs att based on which att was (thumbnail or main ?)
        if ($dm1_parent_guid)
        {
            /*
            $parent_att = new midgard_attachment();
            $parent_att->get_by_guid($dm1_parent_guid);
            */
            $parent_att = mgd_get_object_by_guid($dm1_parent_guid);
            if (!$parent_att->id)
            {
                echo "ERROR: could not fetch attachment object GUID {$dm1_parent_guid} ('parent' to #{$att->id})\n";
                continue;
            }
        }
        else if ($dm1_thumb_guid)
        {
            // PHP5-TODO: Must be copy by value
            $parent_att = $att;
            unset($att);
            /*
            $att = new midgard_attachment();
            $att->get_by_guid($dm1_thumb_guid);
            */
            $att = mgd_get_object_by_guid($dm1_thumb_guid);
            if (!$att->id)
            {
                echo "ERROR: could not fetch attachment object GUID {$dm1_thumb_guid} ('child' to #{$parent_att->id})\n";
                continue;
            }
        }
        else
        {
            // We should not hit this
            echo "ERROR: Something utterly weird happened when trying to handle image datatype attachment #{$att->id}, skipping\n";
            continue;
        }
        $seen_att[$att->id] = true;
        $seen_att[$parent_att->id] = true;

        // Set fieldname and identifier parameters
        $fieldname = $parent_att->parameter('midcom.helper.datamanager.datatype.blob', 'fieldname');
        $att->parameter('midcom.helper.datamanager.datatype.blob', 'fieldname', $fieldname);
        $att->parameter('midcom.helper.datamanager2.type.blobs', 'identifier', 'thumbnail');
        $parent_att->parameter('midcom.helper.datamanager2.type.blobs', 'identifier', 'main');
        // Clear old parent/thumb parameters
        $att->parameter('midcom.helper.datamanager.datatype.image', 'parent_guid', '');
        $parent_att->parameter('midcom.helper.datamanager.datatype.image', 'thumbguid', '');
        // Rest of the parameters and be converted with simple search/replace on the domain
        mgd_param_domain_replace($att, 'midcom.helper.datamanager.datatype.blob', 'midcom.helper.datamanager2.type.blobs');
        mgd_param_domain_replace($parent_att, 'midcom.helper.datamanager.datatype.blob', 'midcom.helper.datamanager2.type.blobs');
        
        $parent_object_guid = mgd_get_att_parent_guid($att);
        $object = mgd_get_object_by_guid($parent_object_guid);
        if (!$object->id)
        {
            echo "ERROR: could not fetch storage object GUID {$parent_object_guid} (parent to attachment #{$att->id})\n";
            continue;
        }
        $guid_list = "thumbnail:{$att->guid},main:{$parent_att->guid}";
        $object->parameter('midcom.helper.datamanager2.type.blobs', "guids_{$fieldname}", $guid_list);
        // We have done everything for the two attachments, so skip to next unseen one
        continue;
    }
    // Collection type needs even more special treatment
    else if ($collection_fieldname)
    {
        /*
        echo "ERROR: Collections not supported ATM\n";
        continue;
        */
        $parent_object_guid = mgd_get_att_parent_guid($att);
        $object = mgd_get_object_by_guid($parent_object_guid);
        if (!$object->id)
        {
            echo "ERROR: could not fetch storage object GUID {$parent_object_guid} (parent to attachment #{$att->id})\n";
            continue;
        }
        $qb_catt = new midgard_query_builder('midgard_attachment');
        $qb_catt->add_constraint('parameter.domain', '=', 'midcom.helper.datamanager.datatype.collection');
        $qb_catt->add_constraint('pid', '=', $object->id);
        $qb_catt->add_constraint('ptable', '=', $object->__table__);
        $collections = $qb_catt->execute();
        $collections_sorted = array();
        foreach ($collections as $tmp_att)
        {
            $att = mgd_get_attachment($tmp_att->id);
            $fieldname = $att->parameter('midcom.helper.datamanager.datatype.collection', 'fieldname');
            $id = $att->parameter('midcom.helper.datamanager.datatype.collection', 'id');
            if (!isset($collections_sorted[$fieldname]))
            {
                $collections_sorted[$fieldname] = array();
            }
            $collections_sorted[$fieldname][$id] = $att;
            $seen_att[$att->id] = true;
        }
        
        /*
        echo "DEBUG: collections_sorted\n===\n";
        print_r($collections_sorted);
        echo "===\n";
        */
        
        foreach ($collections_sorted as $fieldname => $attachments)
        {
            $guid_list = '';
            ksort($attachments);
            foreach ($attachments as $id => $att)
            {
                $identifier = md5("midcom.helper.datamanager.datatype.collection:{$id}:{$att->guid}");
                $guid_list .= ",{$identifier}:{$att->guid}";
                $att->parameter('midcom.helper.datamanager2.type.blobs', 'identifier', $identifier);
                $att->parameter('midcom.helper.datamanager2.type.blobs', 'fieldname', $fieldname);
                mgd_param_domain_replace($att, 'midcom.helper.datamanager.datatype.collection', 'deleteme-midcom.helper.datamanager.datatype.collection');
                mgd_param_domain_replace($att, 'midcom.helper.datamanager.datatype.blob', 'midcom.helper.datamanager2.type.blobs');
            }
            $guid_list = substr($guid_list, 1);
            $object->parameter('midcom.helper.datamanager2.type.blobs', "guids_{$fieldname}", $guid_list);
            echo "INFO: saved following collection: {$guid_list}\n";
        }
        // Collection handled
        continue;
    }
    // Normal blob treatment continued, first rename common domain parameters
    mgd_param_domain_replace($att, 'midcom.helper.datamanager.datatype.blob', 'midcom.helper.datamanager2.type.blobs');
    // Add a dummy identifier
    $identifier = md5("midcom.helper.datamanager.datatype.blob->midcom.helper.datamanager2.type.blobs:{$att->guid}");
    $att->parameter('midcom.helper.datamanager2.type.blobs', 'identifier', $identifier);

    $parent_object_guid = $att->parentguid;
    $object = mgd_get_object_by_guid($parent_object_guid);
    if (!$object->id)
    {
        echo "ERROR: could not fetch storage object GUID {$parent_object_guid} (parent to attachment #{$att->id})\n";
        continue;
    }
    $object->parameter('midcom.helper.datamanager2.type.blobs', "guids_{$fieldname}", "{$identifier}:{$att->guid}");
}

// *** Site specific code below this line *** //

/*
I Left two  examples here, especially the DM1 vs DM2 multiselect converter should be handy
after you edit the domains and names to suit your needs.
*/

// Convert parameters metadata/keywords & metadata/target values CSV to PSV (and add extr pipes to start and end as well fro DM2s benefit)
/*
$qb_md = new midgard_query_builder('midgard_parameter');
$qb_md->add_constraint('domain', '=', 'metadata');
$qb_md->begin_group('OR');
    $qb_md->add_constraint('name', '=', 'keywords');
    $qb_md->add_constraint('name', '=', 'target');
$qb_md->end_group();
$params = $qb_md->execute();
foreach ($params as $param)
{
    $param->value = '|' . str_replace(',', '|', $param->value) . '|';
    if (!$param->update())
    {
        echo "ERROR: Failed to update parameter #{$param->id}\n";
        continue;
    }
    echo "INFO: Updated parameter #{$param->id} from CSV (DM1) to PSV (DM2)\n";
}
*/

// Clean up article expiry
/*
$qb_exp = new midgard_query_builder('midgard_article');
$qb_exp->begin_group('OR');
    $qb_exp->add_constraint('extra1', 'LIKE', '%.%.%');
    $qb_exp->add_constraint('extra3', 'LIKE', '%.%.%');
$qb_exp->end_group();
$articles = $qb_exp->execute();
foreach ($articles as $article)
{
    $fixme = false;
    $regex = '/^\s*[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}\s*$/';
    switch(true)
    {
        case preg_match($regex, $article->extra1):
            $article->extra3 = $article->extra1;
            $article->extra1 = '';
            echo "WARNING: Article #{$article->id} had expiry date in extra1, moved to extra3\n";
            // Fall-trough intentional
        case preg_match($regex, $article->extra3):
            $fixme =& $article->extra3;
            break;
        default:
            // Switches are loops for PHP, thus we need to continue twice
            continue 2;
            break;
    }
    if ($fixme === false)
    {
        echo "ERROR: fixme not set, this should not happen!\n";
        break;
    }
    list ($day, $month, $year) = explode('.', $fixme);
    $tmptime = mktime(23, 59, 59, $month, $day, $year);
    if ($tmptime < mktime(23, 59, 59, 12, 31, 1999))
    {
        $tmptime_hr = date('Y-m-d H:i:s');
        echo "ERROR: Got weird value ({$tmptime_hr}) after converting {$fixme} to unixtime, skipping\n";
        continue;
    }
    $fixme = $tmptime;
    if (!$article->update())
    {
        echo "ERROR: Could not update article #{$article->id}\n";
    }
    echo "INFO: Converted date storage in article #{$article->id}\n";
}
*/

?>