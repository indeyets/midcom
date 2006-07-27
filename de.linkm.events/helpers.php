<?php
/**
 * @package de.linkm.events
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Function for creating nicer time labels for events
 * 
 * Compares start and end times and produces as short time string as possible
 * using locale output.
 * I.e. 21. - 25.02.2005
 *
 * Note: Originally copied from TownPortal snippet /TownPortal/functions
 * 
 * @param timestamp $start     Event starting timestamp.
 * @param timestamp $end       Event ending timestamp.
 * @param boolean $withtime    A control variable to determine whether to add time to the string.
 */
function de_linkm_events_helpers_timelabel ($start, $end, $withtime = false)
{
    $tt=strftime("%x", $start);
    if ($withtime)
    {
        $tt .= " ".date("H:i", $start);
    }
    if ($end > $start)
    {
        if (date("Ymd", $end) == date("Ymd", $start))
        {
            if ($withtime)
            {
                $tt .= " - ".date("H:i", $end);
            }
        } else {
            $tt .= " - ".strftime("%x", $end);
            if ($withtime)
            {
                $tt .= " ".date("H:i", $end);
            }
        }
    }
    return $tt;
}


/**
 * Helper function for getting a sorted and filtered array of event articles
 *
 *
 * @param object $topic        The current Midgard topic object.
 * @param array $config        A key=>value array with the current component configuration.
 * @param boolean $latest      A control variable to determine component behaviour. Used for listing.
 * @param booleam $upcoming    A control variable to determine component behaviour. Used for listing.
 * @return array               The sorted and filtered array of event objects (Midgard articles).
 */
function de_linkm_events_helpers_getarticleids($topic, $config, $latest = false, $upcoming = false)
{
    debug_push("de.linkm.events::getarticleids");

    $ids = array();

    if ($config->get("subdirectory_mode"))
    {
        // Fetch events from all events subdirectories
        $events_topics = array();
        $articles = mgd_list_topic_articles_all($topic->id);
        if ($articles)
        {
            while ($articles->fetch())
            {
                // Check topics status if needed
                if (!array_key_exists($articles->topic, $events_topics))
                {
                    $articles_topic = mgd_get_topic($articles->topic);
                    if ($articles_topic->parameter("midcom","component") === "de.linkm.events")
                    {
                        $events_topics[$articles_topic->id] = true;
                    }
                    else
                    {
                        $events_topics[$articles_topic->id] = false;
                    }
                }
                if ($events_topics[$articles->topic])
                {
                    // This article is a news item
                    if ($articles->title !== "")
                    {
                        $ids[$articles->id] = $articles;
                    }
                }
            }
        }
    } else {
        if ($alist = mgd_list_topic_articles($topic->id))
        {
            while ($alist->fetch())
            {
                if ($alist->title !== "")
                {
                    $art = mgd_get_article($alist->id);
                    $ids[$art->id] = $art;
                }
            }
        }
    }

    if (!$ids || count($ids) === 0)
    {
        debug_add("No Articles found.", MIDCOM_LOG_INFO);
        debug_pop();
        return Array();
    }

    $filter = false;

    if (is_array($_REQUEST)
        && isset($_REQUEST["de_linkm_events_filter"])
        && is_array($_REQUEST["de_linkm_events_filter"]))
    {
        $filter = $_REQUEST["de_linkm_events_filter"];
    }

    $output_ids = Array();

    foreach ($ids as $obj)
    {

        // Time based filtering, done first as it's the fastest check

        $now = time();
        $start = $obj->parameter('midcom.helper.datamanager','data_startdate');

        if (!is_numeric($start) && !empty($start))
        {
            $start = strtotime($start);
        }

        if (is_numeric($obj->extra2))
        {
            $end = $obj->extra2;
        }
        else
        {
            $end = strtotime($obj->extra2);
        }

        if ($upcoming || $latest || !$config->get("index_list_old"))
        {

            // List only on-going and upcoming events

            $sortkey = $config->get("sort_order");
            $sorting = false;

            if ($obj->$sortkey)
            {
                $sorting = $obj->$sortkey;
            } else {
                $sorting = $obj->parameter("midcom.helper.datamanager", "data_$sortkey");
            }

            if (($upcoming || $latest) && !$sorting)
            {
                continue;
            }

            if ($upcoming)
            {
                if ($now > $start)
                {
                    // The event has started, it's no longer up-and-coming
                    continue;
                }
            }

            // The value must be additionally checked for zero

            if ($config->get("index_add_seconds_after_endtime")
                || $config->get("index_add_second_after_endtime") == 0)
            {
                $endbuffer = $config->get("index_add_seconds_after_endtime");
            }
            else
            {
                $endbuffer = 24*3600; //Backwards compatibility
            }

            if ($start < $now)
            {
                // The event has started, further check if it has ended already
                if (($end + $endbuffer) < $now)
                {
                    // The event has ended
                    continue;
                }
            }
        }

        // Date filters
        if ($config->get("enable_datefilter")
            && isset($_REQUEST["de_linkm_events_datefilter"]))
        {
            $df = explode("-", $_REQUEST["de_linkm_events_datefilter"]);
            if (is_array($df))
            {
                if (isset($df[0]) && date("Y", $start) != $df[0])
                {
                    continue;
                }
                if (isset($df[1]) && date("m", $start) != $df[1])
                {
                    continue;
                }
                if (isset($df[2]) && date("d", $start) != $df[2])
                {
                    continue;
                }
            }
        }

        // MidCOM field based filtering (NOTE: Works only if the field is stored into parameter!)

        if (is_array($filter))
        {
            foreach ($filter as $field => $value)
            {
                if ($obj->parameter("midcom.helper.datamanager","data_".$field) != $value)
                {
                    continue(2);
                }
            }
        }

        // Anything that ended here should be clean

        $output_ids[$obj->id] = $obj;

    }

    // Sort the relevant array

    mgd_sort_object_array($output_ids, $config->get("sort_order"));

    debug_pop();
    return $output_ids;
}

?>
