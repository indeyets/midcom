<?php

/**
 * @package net.nemein.simpledb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Simpledb NAP interface class.
 * 
 * @package net.nemein.simpledb
 */
class net_nemein_simpledb_navigation {

  var $_object;
  var $_l10n;
  var $_l10n_midcom;

  function net_nemein_simpledb_navigation() {
    $this->_object = null;
    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $this->_l10n = $i18n->get_l10n("net.nemein.simpledb");
    $this->_l10n_midcom = $i18n->get_l10n("midcom");
  }

  function get_leaves() {

    // list leaves under the current topic. for example:

    $topic = &$this->_object;
    $leaves = array ();

    $qb = midcom_baseclasses_database_article::new_query_builder();
    $qb->add_constraint('topic', '=', $topic->id);
    $qb->add_order('created', 'DESC');
    $result = $qb->execute();
        
    // Prep toolbar
    $toolbar[50] = Array(
        MIDCOM_TOOLBAR_URL => '',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
        MIDCOM_TOOLBAR_ENABLED => true
    );
    $toolbar[51] = Array(
        MIDCOM_TOOLBAR_URL => '',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
        MIDCOM_TOOLBAR_ENABLED => true
    );
    
    foreach ($result as $article) 
    {
           
        // Match the toolbar to the correct URL and check privileges
        $toolbar[50][MIDCOM_TOOLBAR_URL] = "edit/{$article->id}.html";
        $toolbar[50][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:update', $article) == false);
        $toolbar[51][MIDCOM_TOOLBAR_URL] = "delete/{$article->id}.html";
        $toolbar[51][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:update', $article) == false);
        
        $leaves[$article->id] = array (
          MIDCOM_NAV_SITE => Array (
            MIDCOM_NAV_URL => $article->name.".html",
            MIDCOM_NAV_NAME => $article->title),
          MIDCOM_NAV_ADMIN => Array (
            MIDCOM_NAV_URL => "view/" . $article->id,
            MIDCOM_NAV_NAME => $article->title),
          MIDCOM_NAV_GUID => $article->guid,
          MIDCOM_NAV_TOOLBAR => $toolbar,
          MIDCOM_META_CREATOR => $article->creator,
          MIDCOM_META_EDITOR => $article->revisor,
          MIDCOM_META_CREATED => $article->created,
          MIDCOM_META_EDITED => $article->revised
        );
    }
    return $leaves;
  }


  function get_node() {

    // information about the current node (topic)
    $topic = &$this->_object;
    
    // Create Toolbar
    $toolbar[0] = Array
    (
        MIDCOM_TOOLBAR_URL => 'create.html',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create entry'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
        MIDCOM_TOOLBAR_ENABLED => true,
        MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $topic) == false)
    );
    $toolbar[100] = Array(
        MIDCOM_TOOLBAR_URL => '',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
        MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
        MIDCOM_TOOLBAR_ENABLED => true,
        MIDCOM_TOOLBAR_HIDDEN => 
        (    
               $_MIDCOM->auth->can_do('midgard:update', $topic) == false
            || $_MIDCOM->auth->can_do('midcom:component_config', $topic) == false
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

    debug_add ("Component: setting NAP Element to " . $object->name ." [" . $object->id . "]");
    $this->_object = $object;
    return true;
  }


  function get_current_leaf() {
    return $GLOBALS["net_nemein_simpledb_nap_activeid"];
  }

} // navigation

?>