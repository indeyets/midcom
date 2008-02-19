<?php
if (count($argv) != 4)
{
    die("Usage: php find_orphans.php midgardconffile username password\n");
}

$conffile = $argv[1];


if (version_compare(mgd_version(), '1.9.0alpha', '>='))
{
    $midgard = new midgard_connection();
    $midgard->open($conffile);
    //$midgard->set_sitegroup(1);
}
else
{
    mgd_config_init($conffile);
}

mgd_auth_midgard($argv[2], $argv[3]);

//die($_MIDGARD['sitegroup']);

echo "Starting...\n";
foreach ($_MIDGARD['schema']['types'] as $type => $arr)
{
    if ($type == '__midgard_cache')
    {
        continue;
    }
    echo "Seeking orphans for {$type}...\n";
    $orphans = 0;
    $mc = new midgard_collector($type, 'sitegroup', $_MIDGARD['sitegroup']);
    $mc->set_key_property('id');
    
    $up_property = midgard_object_class::get_property_up($type);
    if ($up_property)
    {
        $mc->add_value_property($up_property);
    }
    
    $parent_property = midgard_object_class::get_property_parent($type);
    if ($parent_property)
    {
        $mc->add_value_property($parent_property);
    }

    if (   empty($up_property)
        && empty($parent_property))
    {
        // All objects of $type as root objects...
        continue;
    }

    //mgd_debug_start();
    $mc->execute();
    $objects = $mc->list_keys();
    //mgd_debug_stop();
    foreach ($objects as $object_id => $object_array)
    {
        $up_value = 0;
        $parent_value = 0;
        if ($up_property)
        {
            $up_value = $mc->get_subkey($object_id, $up_property);
        }
        
        if ($parent_property)
        {
            $parent_value = $mc->get_subkey($object_id, $parent_property);
        }
        if (   empty($up_value)
            && empty($parent_value))
        {
            // root level
            continue;
        }
        
        if (is_orphan($type, $up_value, $parent_value))
        {
            echo "    {$type} #{$object_id} is an orphan (up: #{$up_value}, parent: #{$parent_value})";
            $orphans++;
            $obj = new $type($object_id);
            echo "      deleting, ";
            $obj->delete();
            echo mgd_errstr() . "\n";
        }
    }
    
    echo "  {$orphans} orphans out of " . count($objects) . " {$type} objects\n";
}
echo "Done.\n";

function is_orphan($class, $up_value, $parent_value)
{
    $reflector = new midgard_reflection_property($class);
    if (!empty($up_value))
    {
        $up_property = midgard_object_class::get_property_up($class);
        if (!empty($up_property))
        {
            $target_property = $reflector->get_link_target($up_property);
            if (!empty($target_property))
            {
                //echo "DEBUG: midgard_collector('{$class}', '{$target_property}', {$up_value}) \n";
                $mc = new midgard_collector($class, $target_property, $up_value);
                unset($target_property);
                $mc->set_key_property('guid');
                $mc->execute();
                $guids = $mc->list_keys();
                if (empty($guids))
                {
                    unset($mc, $guids);
                    return true;
                }
                list ($parent_guid, $dummy) = each($guids);
                unset($mc, $guids, $dummy);
                return false;
            }
            else
            {
                echo "WARN: Got empty target_property from \$reflector->get_link_target('{$up_property}'), this is schema error\n";
            }
        }
    }
    if (!empty($parent_value))
    {
        $parent_property = midgard_object_class::get_property_parent($class);
        if (!empty($parent_property))
        {
            $target_property = $reflector->get_link_target($parent_property);
            $target_class = $reflector->get_link_name($parent_property);
            if (   !empty($target_property)
                && !empty($target_class))
            {
                //echo "DEBUG: midgard_collector('{$target_class}', '{$target_property}', {$parent_value}) \n";
                $mc = new midgard_collector($target_class, $target_property, $parent_value);
                unset($target_property, $target_class);
                $mc->set_key_property('guid');
                $mc->execute();
                $guids = $mc->list_keys();
                if (empty($guids))
                {
                    unset($mc, $guids);
                    return true;
                }
                list ($parent_guid, $dummy) = each($guids);
                unset($mc, $guids, $dummy);
                return false;
            }
            else
            {
                // TODO: throw warning
                if (empty($target_property))
                {
                    echo "WARN: Got empty target_property from \$reflector->get_link_target('{$up_property}'), this is schema error\n";
                }
                if (empty($target_class))
                {
                    echo "WARN: Got empty target_class from \$reflector->get_link_name('{$parent_property}'), this is schema error\n";
                }
            }
        }
    }
    // No parent/up defined can't be orphan
    return false;
}
?>