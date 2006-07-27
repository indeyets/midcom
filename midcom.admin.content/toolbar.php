<?php

/**
 * @package midcom.admin.content
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class extends the main toolbar class so that it add the view
 * page link where neccessary. It is a subclass, so that it can be easily
 * disabled from components, in cases where no view page link is sensible.
 * 
 * Use it like this, in the example for the request toolbar:
 * 
 * <code>
 * $GLOBALS['midcom_admin_content_toolbar_component']-&gt;disable_view_page();
 * </code>
 * 
 * @package midcom.admin.content 
 */
class midcom_admin_content_toolbar extends midcom_helper_toolbar {
    
    /**
     * Enable auto-view-page-link functionality.
     * 
     * @var bool
     * @access private
     */
    var $_with_view_page = false;
    
    /**
     * Indicates, wether the view page link should be enabled (if possible)
     * or not (in general).
     * 
     * @var bool
     * @access private
     */
    var $_enable_view_page;
    
    /**
     * Initializes the class.
     * 
     * @param bool $with_view_page Set this to true if you want an automatic view page link appended on rendering.
     * @param string $class_style The class style tag for the UL.
     * @param string $id_style The id style tag for the UL.
     */
    function midcom_admin_content_toolbar($with_view_page = false, $class_style = 'midcom_toolbar', $id_style = null) 
    {
        parent::midcom_helper_toolbar($class_style, $id_style);
        $this->_with_view_page = $with_view_page;
        $this->_enable_view_page = true;
    }    
    
    /**
     * Always leave the view page link disabled.
     * 
     * This will only work when the toolbar's view page link has been
     * activated during construction. If it hasn't, it will generate a
     * MidCOM error.
     */
    function disable_view_page()
    {
        if (! $this->_with_view_page)
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, 
                'midcom_admin_content_toolbar: This toolbar does not support view-this-page links. Cannot disable it.');
            // This will exit()
        }
        $this->_enable_view_page = false;
    }
    
    /**
     * Allow the view page link to be enabled if possible.
     * 
     * This will only work when the toolbar's view page link has been
     * activated during construction. If it hasn't, it will generate a
     * MidCOM error.
     */
    function enable_view_page()
    {
        if (! $this->_with_view_page)
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, 
                'midcom_admin_content_toolbar: This toolbar does not support view-this-page links. Cannot enable it.');
            // This will exit()
        }
        $this->_enable_view_page = true;
    }
    
    /**
     * Renders the toolbar.
     * 
     * If the view-page-link feature is enabled, it checks if the link can
     * be enabled in general, and adds the corresponding toolbar buttons then.
     * 
     * @return string The rendered toolbar
     */
    function render()
    {
        if ($this->_with_view_page )
        {
	        $prefix = $_MIDCOM->get_page_prefix();
            $request_data =& $_MIDCOM->get_custom_context_data('request_data');
            // AIS1 support
            if (!is_array($request_data) || !array_key_exists('l10n', $request_data)) 
            {
                $request_data['l10n'] = &$GLOBALS['view_contentmgr']->_l10n;
            }
	        if ($prefix) 
	        {
                // I wonder if ther may be a difference between 
	            $nav = new midcom_helper_nav($_MIDCOM->get_current_context());
	            $view_url = $nav->view_current_page_url($prefix);
	            if (! is_null($view_url) && $this->_enable_view_page) 
	            {
	                $this->add_item(Array(
	                    MIDCOM_TOOLBAR_URL => $view_url,
	                    MIDCOM_TOOLBAR_LABEL => $request_data['l10n']->get("view this page"),
	                    MIDCOM_TOOLBAR_HELPTEXT => null,
	                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/jump-to.png',
	                    MIDCOM_TOOLBAR_ENABLED => true
	                ));
	            } 
	        }
        }
        
        return parent::render();
    }
    
}

?>