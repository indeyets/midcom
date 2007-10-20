<?php
/**
 * @package cc.kaktus.todo
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 4051 2006-09-12 07:32:51Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TODO listing interface class
 *
 * Defines the privilege cc.kaktus.todo::moderation to supersede the original
 * moderator group.
 *
 * @package cc.kaktus.todo
 */
class cc_kaktus_todo_interface extends midcom_baseclasses_components_interface
{
    /**
     * Simple constructor, which links to the parent class
     *
     * @access public
     */
    function cc_kaktus_todo_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'cc.kaktus.todo';

        $this->_autoload_files = array
        (
            'navigation.php',
//            'admin.php',
            'viewer.php',
            'dba_classes/item_dba.php',
        );

        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
        );

        define ('CC_KAKTUS_TODO_TIME_FINISHED', 1);
        define ('CC_KAKTUS_TODO_TIME_OVERTIME', 2);
        define ('CC_KAKTUS_TODO_TIME_FUTURE', 3);
        define ('CC_KAKTUS_TODO_FLAG_IN_PROGRESS', 0);
        define ('CC_KAKTUS_TODO_FLAG_FINISHED', 1);
    }
}
?>