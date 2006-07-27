<?
/**
 * @package midcom.admin.content2
 */
/**
 * This class handles 
 */
class midcom_admin_content2_admincontext extends midcom_baseclasses_components_handler {


    /** 
     * the root_topic of this content tree.
     * @var object midcom_topic
     * */
    var $root_topid = null;
    
    /**
     * the root page of this content tree
     * @var object midcom_tipic 
     */
    var $root_page = null;

    /**
     * The page (for now: topic ) we are editing. The admin module working
     * on that page will relate to that page when relating to urls,nav , etc.
     * @var int context 
     */    
     var $context = null;

    function midcom_admin_content2_admincontext () 
    {
             parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Create the needed context for the admin component.
     * Wishlist: have a midcom_context class to pass to the component instead
     * -> makes testing easier!
     */
    

    function create_context($topic_id) {
    // get root_page and root_topic

        $page = new midcom_baseclasses_database_page($this->config->get("root_page"));
        if (!$page)
        {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "root_page GUID invalid, please check the parameter on the AIS topic");
        }

        $topic = new midcom_db_topic($this->config->get("root_topic"));
        if (!$topic)
        {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "root_topic GUID invalid, please check the parameter on the AIS topic");
        }

        /** @todo understand and remove thse one by one by understanding why they are not needed!
         * that my sound stupid, I know...
         * point is: is there a better way to do this than a context switch?
         *  */
                
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$this->_topic->id}/{$mode}/";

        $this->_context = $_MIDCOM->_create_context();
        $oldcontext = $_MIDCOM->get_current_context();
        
        $_MIDCOM->_set_current_context($this->_context);
        $_MIDCOM->_set_context_data($prefix, MIDCOM_CONTEXT_ANCHORPREFIX);
        $_MIDCOM->_set_context_data("admin", MIDCOM_CONTEXT_SUBSTYLE);
        $_MIDCOM->_set_context_data(MIDCOM_REQUEST_CONTENTADM, MIDCOM_CONTEXT_REQUESTTYPE);
        $_MIDCOM->_set_context_data($this->_root_topic, MIDCOM_CONTEXT_ROOTTOPIC);
        $_MIDCOM->_set_context_data($this->_topic, MIDCOM_CONTEXT_CONTENTTOPIC);
        $_MIDCOM->_set_context_data($this->_topic->parameter("midcom","component"), MIDCOM_CONTEXT_COMPONENT);

        debug_print_r("Context created: ", $_MIDCOM->_context[$this->_context]);
        /** @todo : prepend substyle of component */        
    } 

    /**
     * This function prepares the main content admin toolbar, adding
     * the following buttons to it:
     * 
     * - Create topic (poweruser or topic owner, restriction possible through restrict_create config directive)
     * - Edit topic (poweruser or topic owner)
     * - Delete topic (poweruser or topic owner, restriction possible through restrict_delete config directive)
     * - Manage topic attachments (poweruser or topic owner)
     */
    function _prepare_main_toolbar() 
    {
        $toolbars =& midcom_helper_toolbars::get_instance();
        $toolbar =& $toolbars->top;

        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "{$this->viewdata['admintopicprefix']}topic/create/",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("create subtopic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:create', $this->_topic)
              )
        ));
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "{$this->viewdata['admintopicprefix']}topic/edit/",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("edit topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit-folder.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
              )
        ));
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "{$this->viewdata['admintopicprefix']}topic/score/",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("edit order"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
        )); 
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "{$this->viewdata['admintopicprefix']}topic/delete/",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("delete topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:delete', $this->_topic)
              )
        ));
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "{$this->viewdata['admintopicprefix']}attachment/",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("topic attachments"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midgard:attachments', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
              )
        ));

    }

}