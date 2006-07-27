<?php
/**
 * @package de.linkm.events
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Events Viewer interface class
 * 
 * @todo document
 * 
 * @package de.linkm.events
 */
class de_linkm_events_viewer {

    var $_topic;
    var $_config;
    var $_mode;
    var $_display;
    var $errcode;
    var $errstr;
    var $_metadata;
    var $_latest;
    
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     * 
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;    

    function de_linkm_events_viewer ($topic, $config) {
        $this->_topic = $topic;
        $this->_config = $config;
        $this->_mode = "";
        $this->_display = null;
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        $this->_metadata = null;
        $this->_latest = null;
        $this->_upcoming = null;
        if ($this->_config->get('schemadb') == 'file:/de/linkm/events/config/schemadb_default.dat')
        {
            // Hotfix for a bad config interface
            $this->_config->store(Array('schemadb' => 'file:/de/linkm/events/config/schemadb_default.inc'), false);
        }
        $this->_determine_content_topic();        
    }

    /**
     * Set the content topic to use. This will check against the configuration setting 'symlink_topic'.
     * 
     * @access protected
     */
    function _determine_content_topic() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $guid = $this->_config->get('symlink_topic');
        if (is_null($guid)) 
        {
            // No symlink topic
            // Workaround, we should talk to an DBA object automatically here in fact. 
            $this->_content_topic = new midcom_db_topic($this->_topic->id);
            debug_pop();
            return;
        }
        
        $this->_content_topic = new midcom_db_topic($guid);

        // Validate topic.
                
        if (! $this->_content_topic) 
        {
            debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: ' 
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            $_MIDCOM->generate_error('Failed to open symlink content topic.');
            // This will exit.
        }
        
        if ($this->_content_topic->get_parameter('midcom', 'component') != 'de.linkm.events')
        {
            debug_print_r('Retrieved topic was:', $this->_content_topic);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Symlink content topic is invalid, see the debug level log for details.');
            // This will exit.
        }
        
        debug_pop();
    }
    
    function can_handle ($argc, $argv) {
        if ($argc == 0)
            return true;
        
        if ($argc == 2 && is_numeric($argv[1]) && ($argv[0] === 'latest' || $argv[0] === 'upcoming')) 
            return true;
        
        if ($argc > 2)
            return false;
        
        $display = mgd_get_article_by_name($this->_content_topic->id, $argv[0]);
        if ( (!$display) || ($display->topic != $this->_content_topic->id) )
            return false;
        
        $this->_display = $display;
        $GLOBALS['midcom_component_data']['de.linkm.events']['active_leaf'] = $this->_display->id;

        // Support for old string-formatted dates
        if (!is_numeric($this->_display->extra2)) {
	    $this->_display->extra2 = @strtotime($this->_display->extra2);
        }

        return true;
    }

    function handle ($argc, $argv) {
        if ($argc == 0) {
            $this->_mode = "list";
            $newest_article = $this->_get_newest();
            $this->_metadata = Array (
				      MIDCOM_META_CREATOR => $this->_content_topic->creator,
				      MIDCOM_META_CREATED => $this->_content_topic->created,
				      MIDCOM_META_EDITOR => $newest_article->revisor,
                MIDCOM_META_EDITED => $newest_article->revised
				      );
            $GLOBALS["midcom"]->set_pagetitle($this->_config->get("title"));
            return true;
        } elseif ($argc == 1) {
            $this->_mode = "detail";
            $this->_metadata = Array (
				      MIDCOM_META_CREATOR => $this->_display->creator,
				      MIDCOM_META_CREATED => $this->_display->created,
				      MIDCOM_META_EDITOR => $this->_display->revisor,
                MIDCOM_META_EDITED => $this->_display->revised
				      );
            $GLOBALS["midcom"]->set_pagetitle($this->_display->title);
            $GLOBALS['midcom_component_data']['de.linkm.events']['active_leaf'] = $this->_display->id;
            return true;
        } else {
            $this->_mode = "list";
            $newest_article = $this->_get_newest();
            $this->_metadata = Array (
				      MIDCOM_META_CREATOR => $this->_content_topic->creator,
				      MIDCOM_META_CREATED => $this->_content_topic->created,
				      MIDCOM_META_EDITOR => $newest_article->revisor,
                      MIDCOM_META_EDITED => $newest_article->revised
				      );
            $GLOBALS["midcom"]->set_pagetitle($this->_config->get("title"));
            switch ($argv[0]) {
            case 'upcoming':
                $this->_upcoming = $argv[1];
                break;
            case 'latest':
                $this->_latest = $argv[1];
                break;
            }
            return true;
        }
        die ("We should not come to this line. This is definitly a bug.");
    }

    function show () {

        // get l10n libraries
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $GLOBALS["view_l10n"] = $i18n->get_l10n("de.linkm.events");

        switch ($this->_mode) {
        case "list":
            $this->_show_list();
            break;
        case "detail":
            $this->_show_detail();
            break;
        default:
            die ("We should not reach this line. Event Viewer mode was "
                 . $this->_mode . ", which is not supported.");
            break;
        }
    }

    function _show_list() {
        debug_push("de.linkm.events::show_list");
        
        if (is_null($this->_latest))
            $latest = false;
        else
            $latest = true;

        if (is_null($this->_upcoming))
            $upcoming = false;
        else
            $upcoming = true;
        
        $ids = de_linkm_events_helpers_getarticleids ($this->_content_topic, $this->_config, $latest, $upcoming);
        
        $GLOBALS["view_config"] =& $this->_config;
        
        global $view;
        global $view_id;
        global $view_detail;
        $view_detail = $this->_config->get("enable_details");
        
        if (count($ids) == 0) {
            // No Events Found
            midcom_show_style("events-index-none");
        } else {        
            // Events Found
            midcom_show_style("events-index-init");
            $first = true;
            if ($latest || $upcoming)
                $count = 0;
            
            foreach ($ids as $article) {

                // Support for old string-formatted dates
                if (!is_numeric($article->extra2)) {
                    $article->extra2 = @strtotime($article->extra2);
                }

                $layout = new midcom_helper_datamanager($this->_config->get("schemadb"));

                if (!$layout)
                    $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
						       "Layout class could not be instantinated. de_linkm_events_viewer::_show_detail Aborting.");
                if (!$layout->init($article))
                    $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
						       "Layout class could not be initialized. de_linkm_events_viewer::_show_detail Aborting.");

                $view_id = $article->name;
                $view = $layout->get_array();

                if (isset($view['startdate']))
                {
                    $currMonth = date("Ym", $view['startdate']['timestamp']);
                    if (!isset($prevMonth))
                    {
                        $prevMonth = $currMonth;
                    }
                    if ($prevMonth < $currMonth)
                    {
                        midcom_show_style("events-month-change");
                        $prevMonth = $currMonth;
                    }
                }

                midcom_show_style("events-index-element");

                if ($latest) {
                    $count++;
                    if ($count >= $this->_latest)
                        break;
                }
                if ($upcoming) {
                    $count++;
                    if ($count >= $this->_upcoming)
                        break;
                }
                if ($first) {
                    $GLOBALS["midcom"]->cache->content->expires($view['date']['timestamp']);
                    $first = false;
                }
            }
            midcom_show_style("events-index-finish");
        }
        
        debug_pop();
    }

    function _show_detail() {
        debug_add ("Layout: " . $this->_config->get("schemadb"));
        $layout = new midcom_helper_datamanager($this->_config->get("schemadb"));
        if (!$layout)
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
					       "Layout class could not be instantinated. de_linkm_events_viewer::_show_detail Aborting.");
        if (!$layout->init($this->_display))
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
					       "Layout class could not be initialized. de_linkm_events_viewer::_show_detail Aborting.");
        $GLOBALS["view"] = $layout->get_array();
        $GLOBALS["view_config"] =& $this->_config;
        
        midcom_show_style("events-show-detail");
    }

    function get_metadata() {
        return $this->_metadata;
    }

    function _get_newest() {
        $articles = mgd_list_topic_articles($this->_content_topic->id, "revised");
        if (!$articles)
            return false;
        $articles->fetch();
        return mgd_get_article($articles->id);
    }

}

?>
