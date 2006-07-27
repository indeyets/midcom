<?php
/**
 * Created on Aug 16, 2005
 * @author tarjei huse
 * @package no.bergfald.rcs 
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 * Simple styling class to make html out of diffs and get a simple way 
 * to provide rcs functionality
 * 
 * This handler can be added to your module by some simple steps. Add this to your 
 * request_switch array in the main handlerclass:
 * 
 * <pre>
 *      $rcs_array =  no_bergfald_rcs_handler::get_request_switch();
 *      foreach ($rcs_array as $key => $switch) {
 *            $this->_request_switch[] = $switch;
 *      }
 * </pre>
 *  
 * If you want to have the handler do a callback to your class to add toolbars or other stuff, 
 * 
 * 
 * Links and urls 
 * Linking is done with the format rcs/rcs_action/handler_name/object_guid/<more params>
 * Where handler name is the component using nemein rcs.
 * The handler uses the component name to run a callback so the original handler
 * may control other aspects of the operation - f.x. the Aegir locationbar.
 * 
 * @todo add support for schemas.
 */
 
class no_bergfald_rcs_handler extends midcom_baseclasses_components_handler
{

    /** 
     * Current object Guid. 
     * @var string
     * @access private
     */
    var $_guid = null;
    /**
     * RCS backend
     * @access private
     */
    var $_backend = null;

    /**
     * Pointer to midgard object
     * @var midcom_baseclasses_database_object
     * @access private
     */
    var $_object = null;
    /**
     * Pointer to the toolbars object.
     * @access private
     */
    var $_toolbars = null; 

    /**
     * The args that has been requested.
     */
    var $_args = null;
    
    /**
     * The source component
     */
    var $_source = null;
    
    function no_bergfald_rcs_handler() 
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Static function, returns the request array for the rcs functions.
     * Add this to your _on_initialize function in the calling request:
     * <pre>
     * $rcs_array =  no_bergfald_rcs::get_request_switch();
     * $this->request_switch = array_merge($this->request_switch, $rcs_array)
     * </pre>
     * 
     * @param none
     * @returns array of request params 
     * 
     */
    function get_request_switch() 
    {
        $request_switch = array();
        
        $request_switch[] =  Array
        (
            'fixed_args' => 'rcs',
            'handler' => array('no_bergfald_rcs_handler','history'),
            'variable_args' => 2,
        );
        
        $request_switch[] =  Array
        (
            'fixed_args' => array('rcs','preview'),
            'handler' => array('no_bergfald_rcs_handler','preview'),
            'variable_args' => 3,
        );
        $request_switch[] =  Array
        (
            'fixed_args' => array('rcs', 'diff'),
            'handler' => array('no_bergfald_rcs_handler','diff'),
            'variable_args' => 4,
        );
        $request_switch[] =  Array
        (
            'fixed_args' => array('rcs', 'restore'),
            'handler' => array('no_bergfald_rcs_handler','restore'),
            'variable_args' => 3,
        );
        return $request_switch;
        
    }    

    /**
     * Load the text_diff libaries needed to show diffs.
     */
    function _on_initialize() 
    {
        // It is better to load this libraries here as the component isn't always loaded.
        $_MIDCOM->load_library('midcom.helper.datamanager', 'midcom.helper.xml');    
    }
    
    /**
     * This function setts the correct Aegir navigationclass and
     * /or calls the defined callbacks from the request component.
     * Todo: add a way to get a schema out of this.
     */
    function _do_callbacks() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_source = $this->_args[0];
        $class = str_replace('.', '_' , $this->_args[0]);
        $this->_request_data['source'] = $this->_source;
        
        if (! $_MIDCOM->load_library($this->_args[0]) ) {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not load component {$this->_args[0]}");
        }

        if (array_key_exists('aegir_interface', $this->_request_data)) 
        {
            /*  */
            foreach ($this->_request_data['aegir_interface']->registry as $key => $value)
            {
                if ($value['component'] == $this->_args[0]) 
                {
                    debug_add("Setting active component to $key");
                    $this->_request_data['aegir_interface']->current = $key;
                }
            }
        
            if (class_exists($class. '_aegir_navigation')) 
            {
                $this->_request_data['aegir_interface']->_navigation_class = $class. '_aegir_navigation';
            } else {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "could not find navigation class for $class". '_aegir_navigation');
            }
        } 
        else
        {
            
        
            // FIXME: This part doesn't really work
            /*
            $viewer_class = "{$class}_viewer";
            if (method_exists($viewer_class, 'bergfald_rcs_callback')) 
            {
                debug_add("Calling callback function on class $class");
                $$viewer_class->bergfald_rcs_callback(&$this->_object);
                //call_user_func($class . '_viewer', &$this->_object);
            }*/
        }
        
        debug_pop();
    }
    
    /**
     * Load the object and the rcs backend
     * @param none
     */
    function _load_object() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($this->_guid);
        
        // for now, we only got the aegirrcs handler. Later we might have to reconsider this part.
        $this->_backend = new no_bergfald_rcs_aegirrcs($this->_guid);
        debug_pop();
    }
    /**
     * Call this after loading an object
     */
    function _prepare_toolbars($revision = '') 
    {
        $this->_toolbars = &midcom_helper_toolbars::get_instance();
        $this->_toolbars->top->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "rcs/{$this->_source}/{$this->_guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('Show history'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        if ($revision == '') 
        {
            return;
        }
        $before = $this->_backend->get_prev_version($revision);
        $before2 = $this->_backend->get_prev_version($before);
        $after  = $this->_backend->get_next_version($revision);
        
        if ($before != '' &&$before2 != "") 
        {
            $this->_toolbars->bottom->add_item(
                array
                (
                    MIDCOM_TOOLBAR_URL => "rcs/diff/{$this->_source}/{$this->_guid}/{$before2}/{$before}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n_midcom->get("view %s differences with revision %s"), $before, $before2),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
        $this->_toolbars->bottom->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "rcs/restore/{$this->_source}/{$this->_guid}/{$revision}.html",
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n_midcom->get('restore this revision (%s)'), $revision),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('restore to this version'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED =>
                (
                    $_MIDCOM->auth->can_do('midgard:update', $this->_object)
                )
            )
        );
        $this->_toolbars->bottom->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "rcs/preview/{$this->_source}/{$this->_guid}/{$revision}.html",
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n_midcom->get('preview this revision (%s)'), $revision),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('view the whole version'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        if ($after != '') 
        {
            $this->_toolbars->bottom->add_item(
                array
                (
                    MIDCOM_TOOLBAR_URL => "rcs/diff/{$this->_source}/{$this->_guid}/{$revision}/{$after}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('view differences with next'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('view differences with the next newer version'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_right.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
    }     
    /**
     * Show the changes done to the object 
     */
    function _handler_history($handler_id, $args, &$data) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_args = $args;   
        $this->_do_callbacks();
        $this->_guid = $args[1];
        $this->_prepare_toolbars();
        $this->_load_object();
        
        // Disable the "Show history" button when we're at its view
        $this->_toolbars->top->disable_item("rcs/{$this->_source}/{$this->_guid}/");
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('no.bergfald.rcs');
        
        $this->_request_data['view_title'] = sprintf($this->_request_data['l10n']->get('revision history of %s'), $this->_resolve_object_title());
        $_MIDCOM->set_pagetitle($this->_request_data['view_title']);
        
        
        debug_pop();
        return true;
    }
    
    function _show_history()
    {
        $this->_request_data['history'] = $this->_backend->list_history();
        $this->_request_data['guid']    = $this->_guid;
        midcom_show_style('bergfald-rcs-history');
        
    }
    
    function _resolve_object_title()
    {
        $vars = get_object_vars($this->_object);
        
        if ( array_key_exists('title', $vars) ) 
        {
            return $this->_object->title;
        } 
        elseif ( array_key_exists('name', $vars) ) 
        {
            return $this->_object->name;
        }
        else
        {
            return $this->_object->guid;
        }
    }
    
    /**
     * Show a diff between two versions
     */
    function _handler_diff($handler_id, $args, &$data) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_guid = $args[1];
        $this->_args = $args;
        $this->_do_callbacks(); 
        $this->_load_object();
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('no.bergfald.rcs');        
        
        if (!$this->_backend->version_exists($args[2])
            || !$this->_backend->version_exists($args[3]) ) {
                debug_add("One of the revisions {$args[2]} or  {$args[3]} does not exists. ");
            return false;
        }
        
        if (!class_exists('Text_Diff')) 
        {
            @include_once 'Text/Diff.php';
            @include_once 'Text/Diff/Renderer.php';
            @include_once 'Text/Diff/Renderer/unified.php';
        
            if (!class_exists('Text_Diff')) 
            {
                debug_add("Failed to load tet_diff libraries! These are needed for this handler. " , MIDCOM_LOG_CRIT);
                $this->_request_data['libs_ok'] = false;
                $this->_prepare_toolbars($args[3]);
                debug_pop();
                return true;
            } 
            else 
            {
                $this->_request_data['libs_ok'] = true;
            }
        } 
        else 
        {
                $this->_request_data['libs_ok'] = true;
        }    
        
        $this->_prepare_toolbars($args[3]);        
        $this->_request_data['diff'] = $this->_backend->get_diff($args[2], $args[3]);
        $this->_request_data['comment'] = $this->_backend->get_comment($args[3]);
                
        $this->_request_data['latest_revision'] = $args[3]; 

        $this->_request_data['view_title'] = sprintf($this->_request_data['l10n']->get('changes done in revision %s to %s'), $this->_request_data['latest_revision'], $this->_resolve_object_title());
        $_MIDCOM->set_pagetitle($this->_request_data['view_title']);
        
        debug_pop();
        return true;
        
    }
    function _show_diff() 
    {
        if (!$this->_request_data['libs_ok']) {
            $this->_request_data['error'] = "You are missing the PEAR library Text_Diff that is needed to show diffs.";
            include ('style/bergfald-rcs-error.php');
            return;
        }
        midcom_show_style('bergfald-rcs-diff');
    }
    
    /**
     * Restore to diff
     */
    function _handler_restore($handler_id, $args, &$data) 
    {
        $this->_guid = $args[1];
        $this->_args = $args;
        $this->_do_callbacks();
        $this->_load_object();
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('no.bergfald.rcs');        
        
        $this->_prepare_toolbars($args[2]);       
        
        if ($this->_backend->version_exists($args[2]) && $this->_backend->restore_to_revision($args[2])) {
            $this->_request_data['status'] = true;
        } else {
            $this->_request_data['status'] = false;
        }
        
        return true;
    }
    function _show_restore()
    {
        if ($this->_request_data['status'] == false) 
        {
            $this->_request_data['message'] = $this->_l10n->get("Restore failed.");
            $this->_request_data['message'] .= "<br/>" .$this->_backend->get_error(); 
        } 
        else 
        {
            $this->_request_data['message'] = $this->_l10n->get("Restore sucessfull.");    
        }
        midcom_show_style('bergfald-rcs-restore');
    }
    
    /**
     * View preevies
     */
    function _handler_preview($handler_id, $args, &$data) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        /*1. hente ut ddiff */
        $this->_guid = $args[1];
        $this->_args = $args;
        $this->_do_callbacks();
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('no.bergfald.rcs');
        
        $revision = $args[2];
        
        $this->_load_object();
        $this->_prepare_toolbars($revision);
        $this->_request_data['preview'] = $this->_backend->get_revision($revision);
        
        $this->_request_data['view_title'] = sprintf($this->_request_data['l10n']->get('viewing version %s of %s'), $revision, $this->_resolve_object_title());
        $_MIDCOM->set_pagetitle($this->_request_data['view_title']);        
        
        debug_pop();
        return true;
    }
    
    function _show_preview() 
    {
        midcom_show_style('bergfald-rcs-preview');
    }

    
    
}
?>
