<?php
/**
* @package cc.kaktus.todo
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Navigation subsystem class for cc.kaktus.todo
 * 
 * @package cc.kaktus.todo
 */
class cc_kaktus_todo_navigation extends midcom_baseclasses_components_navigation
{

    /**
     * Constructor.
     * 
     * @access public
     */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Returns the leaf elements, i.e. teams located in the current season.
     * 
     * @access public
     */
    function get_leaves()
    {
        $leaves = array ();
        
        $leaves[CC_KAKTUS_TODO_TIME_FINISHED] = array
        (
            MIDCOM_NAV_SITE => array
            (
                MIDCOM_NAV_URL => 'finished/',
                MIDCOM_NAV_NAME => $this->_l10n->get('finished'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_NAV_GUID => null,
            MIDCOM_NAV_OBJECT => null,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITED  => 0,
            MIDCOM_META_EDITOR  => 0,
        );
        
        $leaves[CC_KAKTUS_TODO_TIME_OVERTIME] = array
        (
            MIDCOM_NAV_SITE => array
            (
                MIDCOM_NAV_URL => 'overtime/',
                MIDCOM_NAV_NAME => $this->_l10n->get('overtime tasks'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_NAV_GUID => null,
            MIDCOM_NAV_OBJECT => null,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITED  => 0,
            MIDCOM_META_EDITOR  => 0,
        );
        
        return $leaves;
    }
    
    function get_node() 
    {
       $toolbar[100] = array
       (
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            (
                   ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
                || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
            )
        );

        return array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_NAV_NOENTRY => false,
            MIDCOM_NAV_CONFIGURATION => $this->_config,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
    }
}
?>