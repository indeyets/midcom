<?php
/**
 * Created on Aug 4, 2005
 * 
 * @package midcom.helper
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:toolbars.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 * Static toolbar class 
 * 
 * This class contains pointers to toolbar objects used in Aegir and (soon) OpenPsa.
 * 
 * Usage:
 * There are some predefined toolbars, they are used for different things:
 * top - the top toolbar
 * bottom - the one below :)
 * aegir_menu - this is used for the menu at the top of aegir2.
 * aegir_location - this is used to show parts of the aegir location bar.
 * To add or edit the toolbars, you access them directly like this:
 * (note the &)
 * <pre>
 * $toolbars = &midcom_helper_toolbars::get_instance();
 * $toolbars->bottom->add_item($menu);
 * $toolbars->top->disable(something);
 * <pre>
 * 
 * And in the rendering code:
 * <pre>
 *  $toolbars->bottom->render();
 * </pre>
 * 
 */
 
class midcom_helper_toolbars 
{    
    /**
     * Link to the top toolbar
     * @var midcom_helper_toolbar
     * @access public
     */
    var $top = null;
    
    
    /**
     * The object toolbar
     * @var midcom_helper_toolbar
     * @access public
     */
     var $bottom = null;
     /**
     * The buttons for different form elements toolbar
     * @var midcom_helper_toolbar
     * @access public
     */
     var $form   = null;
     /**
      * The Aegir dropdown toolbar
      * @access public
      * @var midcom_helper_toolbar
      * 
      */
     var $aegir_menu = null;
     /**
      * The Aegir location array
      * @access public
      * @var midcom_helper_toolbar
      * 
      */
     var $aegir_location = null;
     
    /**
     * Initializes the class.
     * 
     * @param string $class_style The class style tag for the UL.
     * @param string $id_style The id style tag for the UL.
     */
    function midcom_helper_toolbars($class_style = 'midcom_toolbar', $id_style = null) 
    {
        /*
        $GLOBALS['midcom_admin_content_toolbar_main'] = new midcom_admin_content_toolbar(false, 'midcom_toolbar midcom_toolbar_ais_main', null);
        $GLOBALS['midcom_admin_content_toolbar_component'] = new midcom_admin_content_toolbar(true, 'midcom_toolbar', null);
        $GLOBALS['midcom_admin_content_toolbar_meta'] = new midcom_admin_content_toolbar(false, 'midcom_toolbar midcom_toolbar_ais_meta', null);
         */
                 
        $this->top 					= new midcom_helper_toolbar();
        /*
        require_once MIDCOM_ROOT . '/midcom/admin/content/toolbar.php';
        $this->bottom				= new midcom_admin_content_toolbar(true, $class_style, 'midcom_toolbar_bottom');
        */
        $this->meta                 = new midcom_helper_toolbar(false,$class_style . ' midcom_toolbar_ais_meta', 'midcom_toolbar_meta');
        $this->form                 = new midcom_helper_toolbar();
        $this->aegir_menu           = new midcom_helper_toolbar('midcom_toolbar_aegir_menu','ais_top_menu_list');
        $this->aegir_location       = new midcom_helper_toolbar();
    }    
    
    /**
     * Set the site prefix
     */
    function set_prefix($prefix) {
        //$this->bottom->set_prefix($prefix);
    }
    
    /**
     * Renders the top toolbar.
     * 
     * If the view-page-link feature is enabled, it checks if the link can
     * be enabled in general, and adds the corresponding toolbar buttons then.
     * 
     * @return string The rendered toolbar
     */
     function render_top() {
     	return $this->top->render();
     }
    
    /**
     * Renders the object toolbar.
     * 
     * If the view-page-link feature is enabled, it checks if the link can
     * be enabled in general, and adds the corresponding toolbar buttons then.
     * 
     * @return string The rendered toolbar
     */
     function render_bottom() {
     	return $this->bottom->render();
     } 
     
    /**
     * singleton interface, returns the factory instance.
     *
     * @return midcom_admin_toolbar Factory instance
     */
    function &get_instance()
    {
        static $instance = null;
        if (!is_object($instance))
        {
            $instance = new midcom_helper_toolbars();
            //$instance->initialize();
        }
        $ret = &$instance;
        return $ret;
    }   
}
?>