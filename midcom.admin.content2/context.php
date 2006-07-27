<?php
/**
 * Created on Jan 1, 2006
 * @author tarjei huse
 * @package midcom.admin.content2
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
 
/**
 * This class handles a contextswitch between different cotnextes. It is a
 * bit like dynamic load.
 * This class should probably be merged into the application.php framework 
 * in time to provide a simple way to set a context.
 * 
 * Usage:
 * $context = new midcom_admin_content2_context;
 * $context->set_context($id);
 * $context->enter_context();
 * 
 * do_someting_in_context();
 * $context->leave_context();
 * 
 * 
 */
 
class midcom_admin_content2_context {

    /**
     * @var int current context
     */
    //var $currentcontext = null;
    /**
     * contextid -> the id of the context beeing handled. 
     */
    var $contextid = -1;
    
    /**
     * the context to use when this class is done.
     * @var int id of the context
     */
    var $oldcontext;
    /**
     * contexts 
     */
    var $_context = array();
    
    /**
     * The current context's nav object
     * @access public
     * @var object midcom_helper_nav
     */
    var $nav = null;
    
    function midcom_admin_content2_context  ()  {
        $this->contextid = $_MIDCOM->_create_context();
        
    }
    
    /**
     * set a context.
     * @var objectreference topicobject;
     */
    function enter_context(  ) 
    {
        $this->oldcontext = $_MIDCOM->_currentcontext;
        $_MIDCOM->_currentcontext = $this->contextid;
        
        if ($this->nav === null)
        {
            $this->nav = new midcom_helper_nav($this->contextid); 
        }
        if ($_MIDCOM->_context[$this->contextid][MIDCOM_CONTEXT_ROOTTOPIC] === null) 
        {
            $this->set_root_topic(null);
        }
        
        return;
    }
    
    /**
     * leave the context and return to the propper context 
     */
    function leave_context() 
    {
        $_MIDCOM->_currentcontext = $this->oldcontext;
        return;
    }    
    
    /**
     * set a contextparameter
     */
    function set_contextparam($contexttype, $value) 
    {
        $_MIDCOM->_context[$this->contextid][$contexttype] = $value;
    }
    
    /**
     * set the contenttopic for the context
     * @param objectref midcom_topic 
     */
    function set_content_topic(&$topic) 
    {
        $_MIDCOM->_context[$this->contextid][MIDCOM_CONTEXT_CONTENTTOPIC] = &$topic;
        $_MIDCOM->_context[$this->contextid][MIDCOM_CONTEXT_COMPONENT] = $topic->parameter('midcom', 'component');
        
    }
    
    /**
     * Get the current context
     */
    function get_current_context() {
        return $this->contextid;
    }
    
    /**
     * set the root_topic of the context
     * 
     * If the param is empty, the function will try to get the root_topic from
     * the contenttopic (if set) and if not, throw an exception.
     */
    function set_root_topic($root_topic = null) 
    {
        
        if ($root_topic === null) {
            $root_topic = $_MIDCOM->_context[$this->contextid][MIDCOM_CONTEXT_CONTENTTOPIC];
            if ($root_topic === null) 
            {
                $_MIDCOM->generate_error("set_root_topic() called with missing contenttopic and missing root_topic parameter in " . __FILE__);
            }
            while ($root_topic->up != 0) {
                $root_topic = new midcom_db_topic($root_topic->up);
            } 
        }
        
        $_MIDCOM->_context[$this->contextid][MIDCOM_CONTEXT_ROOTTOPIC] = &$root_topic;
    }    
    /**
     * set the context to an administrative context
     */
    function set_admincontext() {
         $this->set_contextparam(MIDCOM_CONTEXT_REQUESTTYPE, MIDCOM_REQUEST_CONTENTADM);
    }
    
}
