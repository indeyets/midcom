<?php
/**
 * @package de.linkm.sitemap
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Sitemap MidCOM Admin class.
 * 
 * @package de.linkm.sitemap
 */

class de_linkm_sitemap_admin {
    
    var $_config;
    var $_topic;
    
    var $_l10n;
    var $_l10n_midcom;
    
    var $errcode;
    var $errstr;
    
    var $_topic_toolbar;

    function de_linkm_sitemap_admin($topic, $config) {
        $this->_config = $config;
        $this->_topic = $topic;
        
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("de.linkm.sitemap");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_topic_toolbar = &$toolbars->top;
        
    }

    function can_handle($argc, $argv) {
        if ($argc == 0)        // no admin yet
            return true;
        elseif ($argc == 1 && $argv[0] == "update_prefs")
            return true;
        else
            return false;
    }

    function handle($argc, $argv) {
        
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        if ($argc == 0)
        {
            return true;
        }
        
        if (!isset ($_REQUEST)) 
        {
            $this->errstr = "No request data found.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOG_INFO);
            return false;
        }
        
        if (array_key_exists ("fpref_submit", $_REQUEST)) 
        {            
            if (! $this->_topic->parameter("de.linkm.sitemap","display_root",$_REQUEST["fpref_display_root"]) ) 
            {
                $error = mgd_errstr();
                if ($error != "Object does not exist") 
                {
                    $this->errstr = "Could not save parameter display_root: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    return false;
                }
            }
            if (! $this->_topic->parameter("de.linkm.sitemap","hide_leaves",$_REQUEST["fpref_hide_leaves"] ) ) 
            {
                $error = mgd_errstr();
                if ($error != "Object does not exist") 
                {
                    $this->errstr = "Could not save parameter hide_leaves: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    return false;
                }
            }
            if (   ! $this->_config->get("hide_leaves") 
                && ! $this->_topic->parameter("de.linkm.sitemap", "leaves_first", $_REQUEST["fpref_leaves_first"] ) ) 
            {
                $error = mgd_errstr();
                if ($error != "Object does not exist") 
                {
                    $this->errstr = "Could not save parameter leaves_first: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    return false;
                }
            }
            if (! $this->_topic->parameter("de.linkm.sitemap", "root_topic", $_REQUEST["fpref_root_topic"] ) ) 
            {
                $error = mgd_errstr();
                if ($error != "Object does not exist") 
                {
                    $this->errstr = "Could not save parameter root_topic: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    return false;
                }
            }
            $GLOBALS["midcom"]->cache->invalidate_all();
        }
        
        $GLOBALS["midcom"]->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
        // This will exit
    }

    function get_metadata() {
        return array (
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITOR  => 0,
            MIDCOM_META_CREATED => time(),
            MIDCOM_META_EDITED  => time()
        );
    }

    function show() {
        global $view_config;
        $view_config = $this->_config;
        midcom_show_style('admin-preferences');
    }

} // admin

    ?>