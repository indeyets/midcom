<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: person_handler.php,v 1.45 2006/07/06 15:47:50 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.contacts search handler and viewer class.
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_handler_search extends midcom_baseclasses_components_handler
{
    var $_toolbars;

    function __construct()
    {
        $this->_toolbars =& midcom_helper_toolbars::get_instance();
        parent::__construct();
    }

    function _on_initialize()
    {
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_search_type($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        switch ($args[0])
        {
            case 'foaf':
                $_MIDCOM->skip_page_style = true;
                $this->_view = 'foaf';
                return true;
        }
        return false;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_search_type($handler_id, &$data)
    {
        if ($this->_view == 'foaf')
        {
            $pres = $this->_search_qb_persons($_GET['search'], false, false);
            if ($pres)
            {
                midcom_show_style('foaf-header');
                foreach ($pres as $person)
                {
                    $GLOBALS['view_person'] = $person;
                    midcom_show_style('foaf-person-item');
                }
                midcom_show_style('foaf-footer');
            }
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_search($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        //We always want to display *something*

        if ($_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_contacts_person'))
        {
            $this->_toolbars->top->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "person/new/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create person'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
        if ($_MIDCOM->auth->can_do('midgard:create', $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group']))
        {
            $this->_toolbars->top->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => 'group/new/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create organization'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }

        return true;
    }

    /**
     * Queries all Contacts objects for $_GET['search']
     *
     * Displays style element 'search-empty' if no results at all
     * can be found
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_search($handler_id, &$data)
    {
        midcom_show_style('search-header');
        if (isset($_GET['search']))
        {
            //Convert asterisks to correct wildcard
            $search = str_replace('*', '%', $_GET['search']);
            $gret = $this->_search_qb_groups($search);
            $pret = $this->_search_qb_persons($search);
            if (   $gret
                || $pret)
            {
                //Some search results got.
            }
            else
            {
                //No results at all (from any of the queries)
                midcom_show_style('search-empty');
            }
        }
        midcom_show_style('search-footer');
    }

    /**
     * Does a QB query for groups, returns false or number of matched entries
     *
     * Displays style element 'search-groups-empty' only if $displayEmpty is
     * set to true.
     */
    function _search_qb_groups($search, $displayEmpty=false, $displayOutput=true, $limit=false, $offset=false)
    {
        if ($search == NULL)
        {
            return false;
        }

        $qb_org = org_openpsa_contacts_group::new_query_builder();
        //$qb_org = new MidgardQuerybuilder('org_openpsa_organization');
        $qb_org->begin_group('OR');

        // Search using only the fields defined in config
        $org_fields = explode(',', $this->_request_data['config']->get('organization_search_fields'));
        if (   !is_array($org_fields)
            || count($org_fields) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Invalid organization search configuration');
        }

        foreach ($org_fields as $field)
        {
            if (empty($field))
            {
                continue;
            }
            $qb_org->add_constraint($field, 'LIKE', '%'.$search.'%');
        }

        $qb_org->end_group();

        //Skip groups in other sitegroups (sitegroup constraint is no longer dropped ?)
        $qb_org->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $results = $qb_org->execute();
        if (   is_array($results)
            && count($results) > 0)
        {
            midcom_show_style('search-groups-header');
            foreach($results as $group)
            {
                //TODO: When we actually use MgdSchema objects just use $group
                //$GLOBALS['view_group'] = new org_openpsa_contacts_group($group->id);
                $GLOBALS['view_group'] = $group;
                midcom_show_style('search-groups-item');
            }
            midcom_show_style('search-groups-footer');
            return count($results);
        }
        else
        {
            //No group results
            if ($displayEmpty==true)
            {
                midcom_show_style('search-groups-empty');
            }
            return false;
        }
    }

    /**
     * Does a QB query for persons, returns false or number of matched entries
     *
     * Displays style element 'search-persons-empty' only if $displayEmpty is
     * set to true.
     */
    function _search_qb_persons($search, $displayEmpty = false, $displayOutput = true, $limit = false, $offset = false)
    {
        if ($search == NULL)
        {
            return false;
        }

        $search = str_replace('*', '%', $search);

        $qb_org = org_openpsa_contacts_person::new_query_builder();
        $qb_org->begin_group('OR');

        // Search using only the fields defined in config
        $person_fields = explode(',', $this->_request_data['config']->get('person_search_fields'));
        if (   !is_array($person_fields)
            || count($person_fields) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Invalid person search configuration');
        }

        foreach ($person_fields as $field)
        {
            if (empty($field))
            {
                continue;
            }
            $qb_org->add_constraint($field, 'LIKE', $search);
        }

        $qb_org->end_group();
        //Skip accounts in other sitegroups (sitegroup constraint is no longer dropped ?)
        $qb_org->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        //mgd_debug_start();
        $results = $qb_org->execute();
        //mgd_debug_stop();
        if (   is_array($results)
            && count($results) > 0)
        {
            if ($displayOutput)
            {
                $_MIDCOM->load_library('org.openpsa.contactwidget');
                midcom_show_style('search-persons-header');
                foreach($results as $person)
                {
                    $GLOBALS['view_person'] = $person;
                    midcom_show_style('search-persons-item');
                }
                midcom_show_style('search-persons-footer');
                return count($results);
            }
            else
            {
                return $results;
            }
        }
        else
        {
            //No group results
            if ($displayEmpty && $displayOutput)
            {
                midcom_show_style('search-persons-empty');
            }
            return false;
        }
    }


}
?>