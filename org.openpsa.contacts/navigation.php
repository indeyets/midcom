<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: navigation.php,v 1.6 2006/02/20 13:53:34 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.org NAP interface class.
 *
 * NAP is mainly used for toolbar rendering in this component
 *
 * ...
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_navigation extends midcom_baseclasses_components_navigation
{
    function _is_initialized()
    {
        $config = false;
        if (org_openpsa_contacts_interface::find_root_group($config))
        {
            return true;
        }
        return false;
    }

    function get_leaves()
    {
        $leaves = array();
        $p_merger = new org_openpsa_contacts_duplicates_merge('person');
        if ($p_merger->merge_needed())
        {
            $leaves['persons_merge'] = array
            (
                MIDCOM_NAV_SITE => array
                (
                    MIDCOM_NAV_URL => "duplicates/person/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('merge persons'),
                ),
                MIDCOM_NAV_ADMIN => array
                (
                    MIDCOM_NAV_URL => "duplicates/person/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('merge persons'),
                ),
                MIDCOM_NAV_GUID => false,
                MIDCOM_META_CREATOR => $_MIDGARD['user'],
                MIDCOM_META_EDITOR => $_MIDGARD['user'],
                MIDCOM_META_CREATED => time(),
                MIDCOM_META_EDITED => time(),
            );
        }
        return $leaves;
    }

    function get_node($toolbar = null)
    {
        $toolbar = Array();
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        return parent::get_node($toolbar);
    }
}

?>