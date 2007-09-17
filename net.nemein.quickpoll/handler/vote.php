<?php
/**
 * @package net.nemein.quickpoll
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 5385 2007-02-19 10:04:06Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Quickpoll vote page handler
 *
 * @package net.nemein.quickpoll
 */

class net_nemein_quickpoll_handler_vote extends midcom_baseclasses_components_handler
{
    /**
     * The content topic to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * The article to operate on
     *
     * @var midcom_db_article
     * @access private
     */
    var $_article = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_quickpoll_handler_vote()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
    }

    /**
     * Displays an article edit view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article,
     */
    function _handler_vote($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_article = new midcom_db_article($args[0]);
        if (! $this->_article)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The article {$args[0]} was not found.");
            // This will exit.
        }
        
        if ($this->_config->get('enable_anonymous'))
        {
            $sudo_mode = true;
            $_MIDCOM->auth->request_sudo('net.nemein.quickpoll');
        }
        else
        {
            // This is wrong, check from actual option instead
            $sudo_mode = false;
            $this->_article->require_do('midgard:create');
        }
        
        if (array_key_exists('net_nemein_quickpoll_option',$_POST))
        {
            $vote = new net_nemein_quickpoll_vote_dba();
            $vote->article = $this->_article->id;
            $vote->selectedoption = $_POST['net_nemein_quickpoll_option'];
            $vote->user = $_MIDGARD['user'];
            $vote->ip = $_SERVER['REMOTE_ADDR'];
            $vote->create();
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Nothing to vote");
        }
        
        if ($sudo_mode)
        {
            $_MIDCOM->auth->drop_sudo();
        }
        
        if (   array_key_exists('net_nemein_quickpoll_vote_return_prefix', $_REQUEST)
            && !empty($_REQUEST['net_nemein_quickpoll_vote_return_prefix']))
        {
            $prefix = $_REQUEST['net_nemein_quickpoll_vote_return_prefix'];
        }
        
        if ($handler_id == 'vote-ajax')
        {
            $_MIDCOM->relocate("{$prefix}ajax/{$args[0]}/");
        }
        $_MIDCOM->relocate("{$prefix}{$args[0]}/");
    }
}

?>