<?php

/**
 * @package net.nehmer.comments
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Comments component.
 *
 * This is a component geared for communities offering a way to add comments on arbitrary
 * pages. It is primairly geared for dl'ed usage. Its regular welcome URL method only
 * shows the configuration interface, commenting the comments topic is prohibited as well.
 *
 * The component stores the data in its own table, indexed by the object guid they are
 * bound to. There is no threading support, comments are orderd by creation date.
 *
 * Commenting is currently only allowed for registered users for security reasons.
 * The user's name and E-Mail will be stored along with the created information in the
 * Metadata in case that the user gets deleted.
 *
 * This component requires Midgard 1.8.
 *
 * <b>Install instructions</b>
 *
 * Just create a topic with this component assigned to it. I recommend dropping it out of
 * your navigation, as the component will by dynamically_loaded always, and the topic
 * itself is only there for configuration purposes.
 *
 * In your component (or style), add a DL line like this wherever you want the comment
 * feature available:
 *
 * $_MIDCOM->dynamic_load('/$path_to_comments_topic/comment/$guid');
 *
 * $guid is the GUID of the object you're commenting.
 *
 *
 *
 * TODO:
 * - Install instruction
 * - Approval
 *
 * @package net.nehmer.comments
 */
class net_nehmer_comments_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nehmer_comments_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'net.nehmer.comments';
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php', 
            'comment.php'
        );
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
            'org.openpsa.notifications',
        );
    }

    /**
     * The delete handler will drop all entries associated with any deleted object
     * so that our DB is clean.
     * 
     * Uses SUDO to ensure privileges.
     */
    function _on_watched_dba_delete($object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $sudo = $_MIDCOM->auth->request_sudo();
        
        $result = net_nehmer_comments_comment::list_by_objectguid($object->guid);
        
        foreach ($result as $comment)
        {
            $comment->delete();
        }
        
        if ($sudo)
        {
            $_MIDCOM->auth->drop_sudo();
        }

        debug_pop();
    }

    /**
     * Reindex everything, try to conserve as much memory as possible.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // TODO
        debug_pop();
    }

}
?>
