<?php
/**
 * @package midcom.admin.content
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This AIS command handles metadata requests for AIS. 
 * 
 * All requests to it redirect to the actual component page on successful processing
 * or display an error message with a return-to-where-you-came link on error.
 * 
 * Its integration is not 100% at this time, as for example the active leaf is lost 
 * during metadata editing.
 * 
 * 
 * 
 * @package midcom.admin.content
 */
class midcom_admin_content__cmdmeta {
    
    /**
     * Argument list to process
     * 
     * @var Array
     * @access private
     */
    var $_argv = null;
    
    /**
     * A reference to the main content admin object.
     * 
     * @var midcom_admin_content_main
     * @access private
     */
    var $_contentadm = null;
    
    /**
     * The topic we are currently editing.
     * 
     * @var MidgardTopic
     * @access private
     */
    var $_topic = null;
    
    /**
     * View mode id.
     * 
     * @var string
     * @access private
     */
    var $_view = '';
    
    /** 
     * The NAP object we are currently editing.
     * 
     * This can be both a leaf and a node.
     * 
     * @var Array
     * @access private
     */
    var $_nap_obj = null;
    
    /**
     * The Content object we are currently working with.
     * 
     * @var MidgardObject
     * @access private
     */
    var $_object = null;
    
    /**
     * The metadata object accociated with the currently edited object.
     * 
     * @var midcom_helper_metadata
     * @access private
     */
    var $_metadata = null;
    
    /**
     * Constructor, initializes the object, nothing fancy.
     * 
     * @param Array $argv The arguments to process
     * @param midcom_admin_content_main A reference to the content admin we are accociated with.
     */
    function midcom_admin_content__cmdmeta ($argv, &$contentadm) 
    {
        $this->_argv = $argv;
        $this->_contentadm = &$contentadm;
        $this->_topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
    }

    /**
     * Main execute handler, see class introduction for available methods.
	 *
	 * Execution is delegated to various helper functions, both in this class,
	 * and directly in midcom_helper_metadata.
	 * 
	 * It will initialize the object to the currently edited metadata object.
	 *
     * @return bool Indicating success
     */
    function execute () {
        debug_push("Content Admin, Meta Command Execute");
        
        if (count($this->_argv) < 2)
        {
            $this->_contentadm->errstr = 'Invalid Metadata Request';
            $this->_contentadm->errcode = MIDCOM_ERRCRIT;
            debug_print_r('Invalid argv:', $this->_argv);
            debug_pop();
            return false;
        }
        
        // Retrieve the NAP object and the corresponding metadata 
        // object we're talking about.
        $nav = new midcom_helper_nav($this->_contentadm->viewdata['context']);
        $this->_nap_obj = $nav->resolve_guid($this->_argv[0]);
        if (! $this->_nap_obj)
        {
            // Revert to the topic NAP object if we can't resolve
            $this->_nap_obj = $nav->get_node($this->_topic->id);
        }
        if (! $this->_nap_obj)
        {
            $this->_contentadm->errstr = 'Failed to retrieve the NAP object for metadata processing. See the debug log for details.';
            $this->_contentadm->errcode = MIDCOM_ERRCRIT;
            debug_print_r('Failed to retrieve NAP object, argv:', $this->_argv);
            debug_pop();
            return false;
        }
        
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($this->_argv[0]);
        if (! $this->_object)
        {
            $this->_contentadm->errstr = 'Failed to retrieve the content object for metadata processing. See the debug log for details.';
            $this->_contentadm->errcode = MIDCOM_ERRCRIT;
            debug_print_r('Failed to retrieve content object, argv:', $this->_argv);
            debug_pop();
            return false;
        }
                
        $this->_metadata = midcom_helper_metadata::retrieve($this->_object);
        if (! $this->_metadata)
        {
            $this->_contentadm->errstr = 'Failed to retrieve the Metadata object for metadata processing. See the debug log for details.';
            $this->_contentadm->errcode = MIDCOM_ERRCRIT;
            debug_print_r('Failed to retrieve Metadata object, content object:', $this->_object);
            debug_pop();
            return false;
        }
        
        // Command Switch
        $return = true;
        switch ($this->_argv[1])
        {
            case 'approve':
                $this->_metadata->approve();
                $this->_return_to_view();
                // This will exit, the break is here to satisfy the IDEs.
                break;
            
            case 'unapprove':
                $this->_metadata->unapprove();
	            $this->_return_to_view();
	            // This will exit, the break is here to satisfy the IDEs.
	            break;

            case 'hide':
                $this->_metadata->set('hide', '1');
                $this->_return_to_view();
	            // This will exit, the break is here to satisfy the IDEs.
	            break;
                
            
            case 'unhide':
                $this->_metadata->set('hide', '');
                $this->_return_to_view();
                // This will exit, the break is here to satisfy the IDEs.
                break;
                
            case 'edit':
                $return = $this->_init_edit();
                break;
                
            default:
                $this->_contentadm->errstr = 'Failed to retrieve the NAP object for metadata processing. See the debug log for details.';
                $this->_contentadm->errcode = MIDCOM_ERRCRIT;
	            debug_print_r('Failed to retrieve NAP object, argv:', $this->_argv);
	            $return = false;
                break;                
        }
        
        debug_pop();
        return $return;
    }
    
    /**
     * This will relocate to the leaf or node that has just been edited.
     * 
     * @access private
     */
    function _return_to_view()
    {
        // Build destination URL.
        $url = "{$this->_contentadm->viewdata['admintopicprefix']}data/";
        if ($this->_nap_obj[MIDCOM_NAV_TYPE] == 'leaf')
        {
            $url .= $this->_nap_obj[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL];
        }
        $GLOBALS['midcom']->relocate($url);
        // This will exit.
    }
    
    /**
     * Handler for DM-based Metadata editing.
     * 
     * @access private
     * @return bool Indicating success
     */
    function _init_edit()
    {
        $dm =& $this->_metadata->get_datamanager();
        switch ($dm->process_form())
        {
	        case MIDCOM_DATAMGR_SAVED:
	        case MIDCOM_DATAMGR_CANCELLED:
                // In both cases, redirect back to the page from where the user came.
                // The DM will invalidate the cache.
                $this->_return_to_view();
                // This will exit, the break is here to satisfy the IDEs.
                break;                
            
	        case MIDCOM_DATAMGR_EDITING:
                // Continue editing
	            break;
	
	        case MIDCOM_DATAMGR_FAILED:
	            $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
	            $this->errcode = MIDCOM_ERRCRIT;
	            debug_pop();
	            return false;
        }
        
        $this->_view = 'edit';
        return true;
    }


    /**
     * Show switch, delegating output to the _show_* methods.
     */
    function show () 
    {
        eval("\$this->_show_$this->_view();");
    }
    
    /**
     * Edit display handler
     * 
     * Two globals prepared:
     * 
     * - <b>view_dm</b>: The Metadata Datamanager.
     * - <b>view_title</b>: The title of the current object.
     * 
     * Calls the style element <b>meta-edit</b>.
     */
    function _show_edit()
    {
        $GLOBALS['view_dm'] =& $this->_metadata->get_datamanager();
        $GLOBALS['view_title'] = $this->_nap_obj[MIDCOM_NAV_NAME];
        midcom_show_style('meta-edit');
    }
}

?>