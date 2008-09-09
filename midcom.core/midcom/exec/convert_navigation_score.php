<?php

/**
 * Update script to convert all legacy topic information into data compatible
 * with the new midgard_topic MgdSchema class from Midgard 1.8.1. 
 * 
 * The script requires admin privileges to execute properly.
 *
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:convert_legacy_metadata.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
$_MIDCOM->auth->require_admin_user();

function reverse_score($topic_id = null)
{
    static $nap = null;
    
    if (!$nap)
    {
        $nap = new midcom_helper_nav();
    }
    
    if (!$topic_id)
    {
        $topic = new midcom_db_topic($GLOBALS['midcom_config']['midcom_root_topic_guid']);
        $topic_id = $topic->id;
    }
    
    $children = $nap->list_child_elements($topic_id);
    
    if (!$children)
    {
        return;
    }
    
    $count = count($children);
    $i = 1;
    
    echo "<ul>\n";
    foreach ($children as $child)
    {
        if ($child[MIDCOM_NAV_TYPE] === 'node')
        {
            $item = $nap->get_node($child[MIDCOM_NAV_ID]);
        }
        else
        {
            $item = $nap->get_leaf($child[MIDCOM_NAV_ID]);
        }
        
        if (   !$item[MIDCOM_NAV_SORTABLE]
            || !$item[MIDCOM_NAV_OBJECT])
        {
            continue;
        }
        
        echo "    <li>\n";
        echo "        {$item[MIDCOM_NAV_NAME]}\n";
        
        if (   $item[MIDCOM_NAV_OBJECT]->id
            && $item[MIDCOM_NAV_OBJECT]->guid)
        {
            $item[MIDCOM_NAV_OBJECT]->metadata->score = $i;
            
            if ($item[MIDCOM_NAV_OBJECT]->update())
            {
                echo " - updated (score: {$i})\n";
            }
            else
            {
                echo " - <span class=\"color: red;\">update FAILED!</span>\n";
            }
            
            $i++;
        }
        
        if ($child[MIDCOM_NAV_TYPE] === 'node')
        {
            reverse_score($child[MIDCOM_NAV_ID]);
        }
        
        echo "    </li>\n";
    }
    echo "</ul>\n";
}

reverse_score();
?>