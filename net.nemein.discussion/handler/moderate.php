<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum moderation handler
 *
 * @package net.nemein.discussion
 */

class net_nemein_discussion_handler_moderate extends midcom_baseclasses_components_handler
{
    /**
     * The thread we're working in
     *
     * @var net_nemein_discussion_thread_dba
     * @access private
     */
    var $_thread = null;

    /**
     * The post which is being moderated
     *
     * @var net_nemein_discussion_post_dba
     * @access private
     */
    var $_post = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['thread'] =& $this->_thread;
        $this->_request_data['post'] =& $this->_parent_post;        
    }


    /**
     * Simple default constructor.
     */
    function net_nemein_discussion_handler_moderate()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Marks post as possible abuse
     */
    function _handler_report($handler_id, $args, &$data)
    {
    
        $this->_post = new net_nemein_discussion_post_dba($args[0]);
        if (!$this->_post)
        {
            return false;
        }
        
        $this->_post->require_do('midgard:update');

        // Report the abuse
        $this->_post->report_abuse();
        
        $this->_thread = $this->_post->get_parent();
        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$this->_thread->name}/");
        // This will exit.
    }
}

?>