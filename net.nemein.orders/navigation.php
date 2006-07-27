<?php
/**
 * @package net.nemein.orders
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Orders NAP interface class.
 * 
 * @package net.nemein.orders
 */
class net_nemein_orders_navigation {
    var $_object;
    var $_config;
    var $_l10n;
    var $_l10n_midcom;
    
    // Permission stuff
    var $_userid;
    var $_user;
    var $_is_mailing_company;
    var $_is_poweruser;
    var $_is_admin;
    var $_mailing_company_group;
    var $_mailing_company_group_guid;
    
    function net_nemein_orders_navigation() {
        $this->_object = null;
        $this->_config = $GLOBALS['midcom_component_data']['net.nemein.orders']['config'];
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.orders");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        
        // Initialize all global authentification variables
        $midgard = mgd_get_midgard();
        $this->_mailing_company_group_guid = '';
        $this->_mailing_company_group = null;
        if (! $midgard->user)
        {
            $this->_userid = null;
            $this->_is_mailing_company = false;
            $this->_is_poweruser = false;
            $this->_is_admin = false;
        }
        else
        {
            $this->_userid = $midgard->user;
            $this->_user = null;
            $this->_is_mailing_company = null;
            
            if ($midgard->admin)
            {
                $this->_is_poweruser = true;
                $this->_is_admin = true;
                $this->_is_mailing_company = false;
            }
            else
            {
                $this->_user = mgd_get_person($this->_userid);
                $this->_is_admin = false;
                $this->_is_poweruser = $this->_user->parameter("Interface","Power_User") != "NO" ? true : false;
                if ($this->_is_poweruser)
                {
                    $this->_is_mailing_company = false;
                }
            }
        }
    } 
    
    function get_leaves() {
        $ret = Array();
        
        $articles = mgd_list_topic_articles($this->_object->id, "score");
        if ($articles) 
        {
            $toolbar = Array();
            if (! $this->_is_mailing_company)
            {
	            $toolbar[50] = Array(
	                MIDCOM_TOOLBAR_URL => '',
	                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit product'),
	                MIDCOM_TOOLBAR_HELPTEXT => null,
	                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
	                MIDCOM_TOOLBAR_ENABLED => true
	            );
            }
            if ($this->_is_poweruser) 
            {
                $toolbar[51] = Array(
                    MIDCOM_TOOLBAR_URL => '',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete product'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                );
            }
            
            while ($articles->fetch()) 
            {
                if (! $this->_is_mailing_company)
                {
                    $toolbar[50][MIDCOM_TOOLBAR_URL] = "product/edit/{$articles->id}.html";
                }
                if ($this->_is_poweruser) 
                {
                    $toolbar[51][MIDCOM_TOOLBAR_URL] = "product/delete/{$articles->id}.html";
                }
                $ret[$articles->id] = array (
                     MIDCOM_NAV_SITE => Array (
                         MIDCOM_NAV_URL => "pub/{$articles->name}.html",
                         MIDCOM_NAV_NAME => $articles->title),
                     MIDCOM_NAV_ADMIN => Array (
                         MIDCOM_NAV_URL => "product/view/{$articles->id}.html",
                         MIDCOM_NAV_NAME => $articles->title),
                     MIDCOM_NAV_GUID => $articles->guid(),
                     MIDCOM_NAV_TOOLBAR => ((count ($toolbar) > 0) ? $toolbar : null),
                     MIDCOM_META_CREATOR => $articles->creator,
                     MIDCOM_META_EDITOR => $articles->revisor,
                     MIDCOM_META_CREATED => $articles->created,
                     MIDCOM_META_EDITED => $articles->revised
                );
            }
        }
        return $ret;
    } 
    
    function get_node() {
        $topic = &$this->_object;
        
        $toolbar = Array();
        if (! $this->_is_mailing_company) 
        {
            $toolbar[10] = Array(
                MIDCOM_TOOLBAR_URL => 'order/query_delivered.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('query delivered orders'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/search.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
        }        
        if ($this->_is_poweruser) 
        {
            /* Add the topic configuration item */
            $toolbar[100] = Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
            /* Add the new article link at the beginning*/
            $toolbar[0] = Array(
                MIDCOM_TOOLBAR_URL => 'product/create.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create product'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
        }
        if ($this->_is_admin) 
        {
            $toolbar[110] = Array(
                MIDCOM_TOOLBAR_URL => 'order/maintain.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('incomplete/corrupt orders'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
        }
        
        return array (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $topic->extra,
            MIDCOM_NAV_TOOLBAR => ((count ($toolbar) > 0) ? $toolbar : null),
            MIDCOM_META_CREATOR => $topic->creator,
            MIDCOM_META_EDITOR => $topic->revisor,
            MIDCOM_META_CREATED => $topic->created,
            MIDCOM_META_EDITED => $topic->revised
        );
    } 
    
    function _load_mailing_group() {
        $guid = $this->_config->get("mailing_company_group");
        if (is_null($guid))
        {
            debug_add('Mailing company group not configured, skipping.');
            $this->_mailing_company_group = null;
            return;
        }
        if ($this->_mailing_company_group_guid == $guid)
        {
            // Do nothing, GUID hasn't changed since last time.
            return;
        }
        $grp = mgd_get_object_by_guid($guid);
        if (! $grp || $grp->__table__ != "grp") 
        {
            debug_add("net.nemein.orders:nap: Could not load Mailing company group, invalid GUID detected: " . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_print_r("net.nemein.orders:nap: Retrieved object was:", $grp);
            $this->_mailing_company_group = null;
            return;
        } 
        $this->_mailing_company_group = $grp;
    }
    
    function set_object($object) {
        $this->_object = $object;
        $this->_config->store_from_object($object, "net.nemein.orders");
        
        // If neccessary, check for the mailing company group:
        // (admins are automatically poweruser, so this is covered here)
        if (! $this->_is_poweruser)
        {
            // Try to load mailing company, this will exit on any error.
            // We do this every time we are set to an object, as the mailing
            // group might change.
            $this->_load_mailing_group();
            if (is_null($this->_mailing_company_group))
            {
                $this->_is_mailing_company = false;
            }
            else
            {
                $this->_is_mailing_company = mgd_is_member($this->_mailing_company_group->id, $this->_user->id);
            }
        }
        
        return true;
    } 
    
} // navigation

?>