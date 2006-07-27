<?php
/**
 * @package net.nemein.organizations
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Group viewer NAP interface class.
 * 
 * @package net.nemein.organizations
 */
class net_nemein_organizations_navigation {

    var $_object;
    var $_l10n;
    var $_l10n_midcom;

    function net_nemein_organizations_navigation() {
        $this->_object = null;
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n('net.nemein.organizations');
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
    }

    function get_leaves() {
        
        $topic = & $this->_object;
        $leaves = array ();
        $group = new midcom_baseclasses_database_group($topic->parameter('net.nemein.organizations', 'group'));

        if (isset($group)) {
          $organizations = mgd_list_groups($group->id);
          
          $qb = midcom_baseclasses_database_group::new_query_builder();
          $qb->add_constraint('owner', '=', $group->id);
          $organizations = $qb->execute();
          
          // Prep toolbar
          $toolbar[50] = Array(
              MIDCOM_TOOLBAR_URL => '',
              MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
              MIDCOM_TOOLBAR_HELPTEXT => null,
              MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
              MIDCOM_TOOLBAR_ENABLED => true
          );
          
          foreach ($organizations as $organization)
          {
               // Match the toolbar to the correct URL and ACL set.
               $toolbar[50][MIDCOM_TOOLBAR_URL] = "edit/{$organization->id}.html";
               $toolbar[50][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:update', $organization) == false);

               if ($organization->official) {
                 $organization_name = $organization->official;
               } else {
                 $organization_name = $organization->name;
               }

               $leaves[$organization->id] = array (
                  MIDCOM_NAV_SITE => Array (
                    MIDCOM_NAV_URL => "{$organization->guid}.html",
                    MIDCOM_NAV_NAME => $organization_name
                  ),
                  MIDCOM_NAV_ADMIN => Array (
                    MIDCOM_NAV_URL => "view/{$organization->id}",
                    MIDCOM_NAV_NAME => $organization_name
                  ),
                  MIDCOM_NAV_GUID => $organization->guid,
                  MIDCOM_NAV_TOOLBAR => $toolbar,
                  MIDCOM_META_CREATOR => 0,
                  MIDCOM_META_EDITOR => 0,
                  MIDCOM_META_CREATED => 0,
                  MIDCOM_META_EDITED => 0
               );
          }
        }
        return $leaves;
    }


    function get_node() {
        
        $topic = & $this->_object;
        
        $toolbar[100] = Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
	        MIDCOM_TOOLBAR_HIDDEN => 
	        (
	               ! $_MIDCOM->auth->can_do('midgard:update', $topic)
	            || ! $_MIDCOM->auth->can_do('midcom:component_config', $topic)
	        )
        );
        
        return array (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_META_CREATOR => $topic->creator,
            MIDCOM_META_EDITOR => $topic->revisor,
            MIDCOM_META_CREATED => $topic->created,
            MIDCOM_META_EDITED => $topic->revised
        );
    }


    function set_object($object) {
        
        debug_add ("Component: setting NAP Element to " . $object->name .
          " [" . $object->id . "]");
        $this->_object = $object;
        return true;
    }

}

?>