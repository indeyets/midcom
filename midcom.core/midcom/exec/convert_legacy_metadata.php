<?php

/**
 * Update script to convert all legacy metadata information into data compatible
 * with the new midcom_helper_metadata classes. 
 * 
 * The script requires admin privileges to execute properly.
 * 
 * You should edit the configuration options at the beginning of the script before execution.
 * 
 * <b>Warning:</b> Any existing metadata created by the new interface will be overwritten.
 * Therefore this script has a die at the beginning by default, to prevent accidential
 * execution. Remove it for the conversion.
 * 
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:convert_legacy_metadata.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

$midgard = mgd_get_midgard();
if (! $midgard->admin)
{
    $_MIDCOM->generate_error(MIDCOM_ERRAUTH, "This script requires admin privileges to run.");
    // This will exit.
}


/* CONFIGURATION START */

// Comment this line to activate this script
die ('The metadata conversion script is disabled by default to prevent accidential execution.');

// Approve all topics.
$approve_all_topics = true;

// Approve all non-articles.
$GLOBALS['approve_all_unsupported_leaves'] = true;

// Approve all unapproved articles 
$GLOBALS['approve_all_unapproved_articles'] = false;

// Do Aegir scheduling/approval conversion
$GLOBALS['convert_aegir'] = true;

// Dump a detailed log during upgrading
$GLOBALS['verbose'] = true;

/* CONFIGURATION END */

/**
 * @ignore
 */
function status ($string)
{
    if ($GLOBALS['verbose'])
    {
        echo "{$string}\n";
    }
}

/**
 * @ignore
 */
function convert_article_approval($topic_id)
{
    status ('    Copying article approval information');
    $articles = mgd_list_topic_articles($topic_id);
    if ($articles)
    {
        while ($articles->fetch())
        {
            $article = mgd_get_article($articles->id);
            $guid = '';
            if ($article->approver != 0)
            {
                $person = mgd_get_person($article->approver);
                if (! $person)
                {
                    status ("<span style='color:red;'>        Article {$article->id} had a broken approver id {$person->approver} (using current user as fallback): " . mgderrstr() . '</span>');
                    $person = mgd_get_person($midgard->user);
                }
            }
            if ($GLOBALS['approve_all_unapproved_articles'] && ! $article->approved)
            {
                $meta =& midcom_helper_metadata::retrieve($article);
                $meta->approve();
            }
            else
            {
                $article->parameter('midcom.helper.metadata', 'approved', $article->approved);
                $article->parameter('midcom.helper.metadata', 'approver', $guid);
            }
        }
    }
}

/*
* @ignore
*/
function hide_unapproved_photos($topic)
{
    // TODO: this supports only local gallery config, not the one in sitegroup-config
    if ($topic->parameter("net.siriux.photos", "only_approved"))
    {
        $articles = mgd_list_topic_articles($topic->id);
        while (   $articles
               && $articles->fetch())
        {
            if (!$articles->approved)
            {
                $articles->parameter("midcom.helper.metadata", "hide", 1);
            }
        }
    }
}

/**
* @ignore
*/
function convert_aegir($node_id)
{
    $nap = new midcom_helper_nav();
    if ($GLOBALS['convert_aegir'])
    {
        status ('    Converting Aegir scheduling/approval now');
        $leaves = $nap->list_leaves($node_id);
        foreach ($leaves as $leaf_id)
        {
            $leaf = $nap->get_leaf($leaf_id);
            $meta =& midcom_helper_metadata::retrieve($nap->get_leaf($leaf_id));
            $object = $meta->object;
            
            // Approval
            if (   $object->__table__ == 'page'
                || $object->__table__ == 'style'
                || $object->__table__ == 'snippetdir')
            {
                $status = $object->parameter('approval', 'status');
                if ($status == 'always' || $status == 'now')
                {
                    $meta->approve();
                    status("        Approved object {$leaf[MIDCOM_NAV_NAME]}.");
                }
                else
                {
                    $meta->unapprove();
                    status("        Unapproved object {$leaf[MIDCOM_NAV_NAME]}.");
                }
            }
            
            // Scheduling
            $start = 0;
            $end = 0;
            // Two cases, the first one is an article, some strange
            // combination of built-in scheduling and parameters are used here.
            if ($object->__table__ == 'article')
            {
                $start = $object->startdate;
                $end = $object->enddate;
                $start += $object->parameter('approve_time', 'starthours') * 60 * 60;
                $start += $object->parameter('approve_time', 'startminutes') * 60;
                $end += $object->parameter('approve_time', 'endhours') * 60 * 60;
                $end += $object->parameter('approve_time', 'endminutes') * 60;
            }
            else
            {
                $start = $object->parameter('approval', 'startdate');
                $end = $object->parameter('approval', 'enddate');
            }
            $meta->set('schedule_start', $start);
            $meta->set('schedule_end', $end);
            
            if ($start || $end)
            {
                $start = strftime("%x %X", $start);
                $end = strftime("%x %X", $end);
                status("        Scheduled leaf {$leaf[MIDCOM_NAV_NAME]} from {$start} to {$end}.");
            }
            
        }
    }
} 

/**
 * @ignore
 */
function approve_unsupported_leaves($node_id)
{
    $nap = new midcom_helper_nav();
    if ($GLOBALS['approve_all_unsupported_leaves'])
    {
        status ('    Approving all unsupported leaves now');
        $leaves = $nap->list_leaves($node_id);
        foreach ($leaves as $leaf_id)
        {
            $leaf = $nap->get_leaf($leaf_id);
            $meta =& midcom_helper_metadata::retrieve($nap->get_leaf($leaf_id));
            if ($meta)
            {
                $meta->approve();
            }
            else
            {
                status("<span style='color:red;'>    Failed to retrieve Meta object for leaf {$leaf[MIDCOM_NAV_NAME]}, skipping.</span>"); 
            }
        }
    }
} 

/**
 * @ignore
 */
function convert_leaf_hiding($node)
{
    status ('    Converting article hiding.');
    $nap = new midcom_helper_nav();
    $leaves = $nap->list_leaves($node[MIDCOM_NAV_ID]);
    foreach ($leaves as $leaf_id)
    {
        $leaf = $nap->get_leaf($leaf_id);
        $meta =& midcom_helper_metadata::retrieve($nap->get_leaf($leaf_id));
        if (strtolower($meta->object->parameter($node[MIDCOM_NAV_COMPONENT], 'visible')) == 'no')
        {
            $meta->set('nav_noentry', true);
            status ("        Leaf {$leaf[MIDCOM_NAV_NAME]} not shown in navigation.");
        }
        else
        {
            $meta->set('nav_noentry', false);
            status ("        Leaf {$leaf[MIDCOM_NAV_NAME]} shown in navigation.");
        }
    }
    
}

echo "<pre>\n";

// Allow us to check everything, you never know.
$GLOBALS['midcom_config']['show_hidden_objects'] = true;
$GLOBALS['midcom_config']['show_unapproved_objects'] = true;

$nap = new midcom_helper_nav();
$nodes = Array();
$nodeid = $nap->get_root_node();

while (! is_null($nodeid))
{
    $node = $nap->get_node($nodeid);
    $topic = $node[MIDCOM_NAV_OBJECT];
    $node_meta = midcom_helper_metadata::retrieve($topic);
 
    status ("Processing topic {$nodeid}: {$node[MIDCOM_NAV_NAME]} ({$node[MIDCOM_NAV_COMPONENT]}) ...");
    
    // Approval and the old-style visibility checks:   
    if ($approve_all_topics)
    {
        status ('    Approving topic');
        $node_meta->approve();
    }
    if (strtolower($topic->parameter($node[MIDCOM_NAV_COMPONENT], 'visible')) == 'no')
    {
        $node_meta->set('hide', true);
    }
    else
    {
        $node_meta->set('hide', false);
    }
    
    convert_aegir($nodeid);
    convert_leaf_hiding($node);
    
    switch ($node[MIDCOM_NAV_COMPONENT])
    {
        case 'de.linkm.events':
        case 'net.nemein.discussion':
        case 'net.nemein.downloads':
        case 'net.nemein.quickpoll':
        case 'de.linkm.newsticker':
        case 'de.linkm.taviewer':
        case 'net.nemein.orders':
        case 'net.nemein.rss':
        case 'net.nemein.simpledb':
        case 'no.odindata.quickform':
            convert_article_approval($nodeid);
        break;
        case 'net.siriux.photos':
            convert_article_approval($nodeid);
            hide_unapproved_photos($node[MIDCOM_NAV_OBJECT]);
            break;
            
        case 'net.nemein.calendar':
        case 'net.nemein.organizations':
        case 'net.nemein.personnel':
        case 'net.nemein.registrations':
        case 'net.nemein.reservations':
            approve_unsupported_leaves($nodeid);
            break;
        
        case 'net.nemein.wiki':
            convert_article_approval($nodeid);
            
            // Convert Navigation hiding (extra2 by default)
            status ('    Converting wiki display-in-navigation flag.');
            $articles = mgd_list_topic_articles($nodeid);
            if ($articles)
            {
                while ($articles->fetch())
                {
                    $article = mgd_get_article($articles->id);
                    if (! $article->extra2)
                    {
                        $article->parameter('midcom.helper.metadata', 'nav_noentry', true);
                        status ("        Article {$article->id} not shown in navigation.");
                    }
                    else
                    {
                        $article->parameter('midcom.helper.metadata', 'nav_noentry', false);
                        status ("        Article {$article->id} shown in navigation.");
                    }
                }
            }
            break;
            
        // This is the rest of the known components:
        case 'de.linkm.collector':
        case 'de.linkm.sitemap':
        case 'net.nemein.incidentdb':
        case 'net.nemein.supportview':
            status ('    Nothing else to do for this component.');
            break;
        
        default:
            status ('<span style="color:orange;">    Unknown component, skipping it.</span>');
            break;
    }
    
    // Retrieve all child nodes and append them to $nodes:
    $childs = $nap->list_nodes($nodeid);
    if ($childs === false)
    {
        echo "</pre>\n";
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to list the child nodes of {$nodeid}. Aborting.");
    } 
    $nodes = array_merge($nodes, $childs);
    $nodeid = array_shift($nodes);
}



echo "\n\nData Conversion complete.\n\n";
echo "\n\nInvalidating the Content Cache...\n\n";
$_MIDCOM->cache->invalidate_all();
echo '</pre>';

?>
